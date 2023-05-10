<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
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
        if (is_writable(WWW_ROOT . 'img' . DS . 'no-avatar.jpg')) {
            unlink(WWW_ROOT . 'img' . DS . 'no-avatar.jpg');
        }
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
     * @test
     * @uses \MeCms\Model\Entity\User::_getFullName()
     */
    public function testGetFullNameVirtualField(): void
    {
        $this->Entity->set(['first_name' => 'Alfa', 'last_name' => 'Beta']);
        $this->assertEquals('Alfa Beta', $this->Entity->get('full_name'));
    }

    /**
     * @test
     * @uses \MeCms\Model\Entity\User::_getLastLogins()
     */
    public function testLastLoginsGetAccessor(): void
    {
        $result = $this->Entity->get('last_logins');
        $this->assertEmpty($result);

        $data = [
            'platform' => 'Linux',
            'browser' => 'Chrome',
            'version' => '55.0.2883.87',
            'agent' => null,
            'ip' => '',
            'time' => time(),
        ];
        $result = $this->Entity->set('last_logins', [$data])->get('last_logins');
        $this->assertCount(1, $result);
        $row = array_value_first($result);
        $this->assertInstanceOf(FrozenTime::class, $row['time']);
        $row['time'] = (int)$row['time']->toUnixString();
        $this->assertEquals($data, $row);
    }

    /**
     * @test
     * @uses \MeCms\Model\Entity\User::_getPicture()
     */
    public function testGetPictureVirtualField(): void
    {
        $this->assertEquals('MeCms.no-avatar.jpg', $this->Entity->set('id', 1)->get('picture'));

        $Filesystem = new Filesystem();
        $Filesystem->createFile(WWW_ROOT . 'img' . DS . 'no-avatar.jpg');
        $this->assertEquals('no-avatar.jpg', $this->Entity->get('picture'));

        $id = 0;
        foreach (['jpg', 'jpeg', 'gif', 'png', 'JPEG'] as $extension) {
            $Filesystem->createFile(WWW_ROOT . 'img' . DS . 'users' . DS . ++$id . '.' . $extension);
            $this->assertEquals('users' . DS . $id . '.' . $extension, $this->Entity->set('id', $id)->get('picture'));
        }
    }
}
