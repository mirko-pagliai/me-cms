<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace MeCms\Test\TestCase\Model\Entity;

use Cake\Collection\Collection;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use MeCms\TestSuite\EntityTestCase;
use Tools\Filesystem;

/**
 * UserTest class
 */
class UserTest extends EntityTestCase
{
    /**
     * Called after every test method
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Filesystem::instance()->unlinkRecursive(USER_PICTURES, 'empty', true);
        @unlink(WWW_ROOT . 'img' . DS . 'no-avatar.jpg');
    }

    /**
     * Test for fields that cannot be mass assigned
     * @test
     */
    public function testNoAccessibleProperties(): void
    {
        $this->assertHasNoAccessibleProperty(['id', 'post_count', 'modified']);
    }

    /**
     * Test for `_getFullName()` method
     * @test
     */
    public function testGetFullNameVirtualField(): void
    {
        $this->Entity->set(['first_name' => 'Alfa', 'last_name' => 'Beta']);
        $this->assertEquals('Alfa Beta', $this->Entity->get('full_name'));
    }

    /**
     * Test for `_getLastLogins()` method
     * @test
     */
    public function testLastLoginsGetAccessor(): void
    {
        $entity = new Entity([
            'platform' => 'Linux',
            'browser' => 'Chrome',
            'version' => '55.0.2883.87',
            'agent' => null,
            'ip' => '',
            'time' => new FrozenTime(),
        ]);

        $result = $this->Entity->set('last_logins', [$entity])
            ->get('last_logins');
        $this->assertInstanceof(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame($entity, $result->first());
    }

    /**
     * Test for `_getPicture()` method
     * @test
     */
    public function testGetPictureVirtualField(): void
    {
        $this->assertEquals('MeCms.no-avatar.jpg', $this->Entity->set('id', 1)->get('picture'));

        $Filesystem = new Filesystem();

        $Filesystem->createFile(WWW_ROOT . 'img' . DS . 'no-avatar.jpg', null);
        $this->assertEquals('no-avatar.jpg', $this->Entity->get('picture'));

        $id = 0;
        foreach (['jpg', 'jpeg', 'gif', 'png', 'JPEG'] as $extension) {
            $Filesystem->createFile(WWW_ROOT . 'img' . DS . 'users' . DS . ++$id . '.' . $extension);
            $this->assertEquals('users' . DS . $id . '.' . $extension, $this->Entity->set('id', $id)->get('picture'));
        }
    }
}
