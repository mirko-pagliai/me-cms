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
namespace MeCms\Test\TestCase\Controller\Admin;

use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\Tag;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PostsControllerTest class
 */
class PostsControllerTest extends ControllerTestCase
{
    /**
     * @var array
     */
    protected static $example = [
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'new post title',
        'slug' => 'new-post-slug',
        'text' => 'new post text',
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
        'plugin.MeCms.Users',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        create_kcfinder_files();

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        safe_unlink_recursive(KCFINDER, 'empty');

        parent::tearDown();
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['add', 'edit', 'index'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertNotEmpty($this->viewVariable('categories'));
            $this->assertNotEmpty($this->viewVariable('users'));
        }
    }

    /**
     * Tests for `beforeFilter()` method, with no categories
     * @test
     */
    public function testBeforeFilterNoCategories()
    {
        //Deletes all categories
        $this->Table->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'PostsCategories', 'action' => 'index']);
            $this->assertFlashMessage('You must first create a category');
        }
    }

    /**
     * Tests for `beforeFilter()` method, with no users
     * @test
     */
    public function testBeforeFilterNoUsers()
    {
        //Deletes all users
        $this->Table->Users->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'Users', 'action' => 'index']);
            $this->assertFlashMessage('You must first create an user');
        }
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        foreach (['add', 'edit'] as $action) {
            $this->assertHasComponent('KcFinder', $action);
        }
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => true,
        ]);

        //With `edit` and `delete` actions
        foreach (['edit', 'delete'] as $action) {
            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => true,
                'user' => false,
            ], $action);
        }

        //With `edit` action and an user who owns the record
        $this->Controller->request = $this->Controller->request->withParam('pass.0', 1);
        $this->assertUsersAreAuthorized([
            1 => true,
            2 => false,
            3 => false,
            4 => false,
        ], 'edit');

        $this->Controller->request = $this->Controller->request->withParam('pass.0', 2);
        $this->assertUsersAreAuthorized([
            1 => false,
            2 => false,
            3 => false,
            4 => true,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Posts/index.ctp');
        $this->assertContainsInstanceof(Post::class, $this->viewVariable('posts'));
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Posts/add.ctp');
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));

        //POST request. Data are valid
        $this->post($url, self::$example);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Posts/edit.ctp');
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
        $this->assertContainsInstanceof(Tag::class, $this->viewVariable('post')->tags);

        //Checks if the `created` field has been properly formatted
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}$/', $this->viewVariable('post')->created);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceof(Post::class, $this->viewVariable('post'));
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(1)->isEmpty());
    }

    /**
     * Tests that the admins and manangers can add and edit as another user
     * @test
     */
    public function testAdminsAndManagersCanAddAndEditAsAnotherUser()
    {
        foreach (['admin', 'manager'] as $userGroup) {
            $this->setUserGroup($userGroup);

            foreach ([1, 2] as $userId) {
                //Adds record
                $this->post($this->url + ['action' => 'add'], ['user_id' => $userId] + self::$example);
                $this->assertRedirect(['action' => 'index']);
                $this->assertFlashMessage(I18N_OPERATION_OK);

                $post = $this->Table->find()->last();
                $this->assertEquals($userId, $post->user_id);

                //Edits record, adding +1 to the `user_id`
                $this->post($this->url + ['action' => 'edit', $post->id], ['user_id' => ++$userId] + self::$example);
                $this->assertRedirect(['action' => 'index']);
                $this->assertFlashMessage(I18N_OPERATION_OK);

                $post = $this->Table->findById($post->id)->first();
                $this->assertEquals($userId, $post->user_id);

                $this->Table->delete($post);
            }
        }
    }

    /**
     * Tests that the other users cannot add and edit as another user
     * @test
     */
    public function testOtherUsersCannotAddOrEditAsAnotherUser()
    {
        $this->setUserGroup('user');
        $this->setUserId(3);

        foreach ([1, 2] as $userId) {
            //Adds record
            $this->post($this->url + ['action' => 'add'], ['user_id' => $userId] + self::$example);
            $this->assertRedirect(['action' => 'index']);
            $this->assertFlashMessage(I18N_OPERATION_OK);

            $post = $this->Table->find()->last();
            $this->assertEquals(3, $post->user_id);

            //Edits record, adding +1 to the `user_id`
            $this->post($this->url + ['action' => 'edit', $post->id], ['user_id' => ++$userId] + self::$example);
            $this->assertRedirect(['action' => 'index']);
            $this->assertFlashMessage(I18N_OPERATION_OK);

            $post = $this->Table->findById($post->id)->first();
            $this->assertEquals(3, $post->user_id);

            $this->Table->delete($post);
        }
    }
}
