<?php
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

use MeCms\Model\Entity\User;
use MeTools\TestSuite\TestCase;

/**
 * UserTest class
 */
class UserTest extends TestCase
{
    /**
     * @var \MeCms\Model\Entity\User
     */
    protected $User;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->User = new User;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        foreach ([
            WWW_ROOT . 'img' . DS . 'no-avatar.jpg',
            WWW_ROOT . 'img' . DS . 'users' . DS . '1.jpg',
        ] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Test for fields that cannot be mass assigned using newEntity() or
     *  patchEntity()
     * @test
     */
    public function testNoAccessibleProperties()
    {
        $this->assertFalse($this->User->isAccessible('id'));
        $this->assertFalse($this->User->isAccessible('post_count'));
        $this->assertFalse($this->User->isAccessible('modified'));
    }

    /**
     * Test for virtual fields
     * @test
     */
    public function testVirtualFields()
    {
        $this->assertEquals(['full_name', 'picture'], $this->User->getVirtual());
    }

    /**
     * Test for `_getFullName()` method
     * @test
     */
    public function testFullNameGetMutator()
    {
        $this->User->first_name = 'Alfa';
        $this->User->last_name = 'Beta';
        $this->assertEquals($this->User->first_name . ' ' . $this->User->last_name, $this->User->full_name);
    }

    /**
     * Test for `_getPicture()` method
     * @test
     */
    public function testPictureGetMutator()
    {
        $this->User->id = 1;

        $this->assertEquals(ME_CMS . '.no-avatar.jpg', $this->User->picture);

        $filename = WWW_ROOT . 'img' . DS . 'no-avatar.jpg';
        file_put_contents($filename, null);
        $this->assertEquals('no-avatar.jpg', $this->User->picture);

        $filename = WWW_ROOT . 'img' . DS . 'users' . DS . '1.jpg';
        file_put_contents($filename, null);
        $this->assertEquals('users' . DS . '1.jpg', $this->User->picture);
    }
}
