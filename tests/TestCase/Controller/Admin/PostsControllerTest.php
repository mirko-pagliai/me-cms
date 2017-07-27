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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Controller\Admin\PostsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PostsControllerTest class
 */
class PostsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\Admin\PostsController
     */
    protected $Controller;

    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * @var array
     */
    protected $example;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.posts_tags',
        'plugin.me_cms.tags',
        'plugin.me_cms.users',
    ];

    /**
     * @var array
     */
    protected $url;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->example = [
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'new post title',
            'slug' => 'new-post-slug',
            'text' => 'new post text',
        ];

        $this->setUserGroup('admin');

        $this->Controller = new PostsController;

        $this->Posts = TableRegistry::get(ME_CMS . '.Posts');

        Cache::clear(false, $this->Posts->cache);
        Cache::clear(false, $this->Posts->Users->cache);

        $this->url = ['controller' => 'Posts', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        foreach (['add', 'edit', 'index'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
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
        $this->Posts->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
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
        $this->Posts->Users->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get(array_merge($this->url, compact('action'), [1]));
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
            $this->Controller = new PostsController;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);
            $this->Controller->initialize();

            $this->assertContains('KcFinder', $this->Controller->components()->loaded());
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

        //`edit` and `delete` actions
        foreach (['edit', 'delete'] as $action) {
            $this->Controller = new PostsController;
            $this->Controller->Posts = $this->Posts;
            $this->Controller->request = $this->Controller->request->withParam('action', $action);

            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => true,
                'user' => false,
            ]);
        }

        //`edit` action, with an user who owns the record
        $this->Controller = new PostsController;
        $this->Controller->Posts = $this->Posts;
        $this->Controller->request = $this->Controller->request
            ->withParam('action', 'edit')
            ->withParam('pass.0', 1);

        $this->assertUsersAreAuthorized([
            1 => true,
            2 => false,
            3 => false,
            4 => false,
        ]);

        $this->Controller->request = $this->Controller->request
            ->withParam('pass.0', 2);

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
        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Posts/index.ctp');

        $postsFromView = $this->viewVariable('posts');
        $this->assertNotEmpty($postsFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postsFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Posts/add.ctp');

        $postFromView = $this->viewVariable('post');
        $this->assertNotEmpty($postFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);

        //POST request. Data are valid
        $this->post($url, $this->example);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $postFromView = $this->viewVariable('post');
        $this->assertNotEmpty($postFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);
    }

    /**
     * Tests for `edit()` method
     * @test
     */
    public function testEdit()
    {
        $url = array_merge($this->url, ['action' => 'edit', 1]);

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Posts/edit.ctp');

        $postFromView = $this->viewVariable('post');
        $this->assertNotEmpty($postFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);

        //Checks if the `created` field has been properly formatted
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}$/', $postFromView->created);

        //Checks for tags
        $this->assertNotEmpty($postFromView->tags);
        $this->assertInstanceof('MeCms\Model\Entity\Tag', $postFromView->tags);

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        $postFromView = $this->viewVariable('post');
        $this->assertNotEmpty($postFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Post', $postFromView);
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->post(array_merge($this->url, ['action' => 'delete', 1]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
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
                $this->post(
                    array_merge($this->url, ['action' => 'add']),
                    array_merge($this->example, ['user_id' => $userId])
                );
                $this->assertRedirect(['action' => 'index']);
                $this->assertFlashMessage('The operation has been performed correctly');

                $post = $this->Posts->find()->last();
                $this->assertEquals($userId, $post->user_id);

                //Edits record, adding +1 to the `user_id`
                $this->post(
                    array_merge($this->url, ['action' => 'edit', $post->id]),
                    array_merge($this->example, ['user_id' => $userId + 1])
                );
                $this->assertRedirect(['action' => 'index']);
                $this->assertFlashMessage('The operation has been performed correctly');

                $post = $this->Posts->findById($post->id)->first();
                $this->assertEquals($userId + 1, $post->user_id);

                $this->Posts->delete($post);
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
            $this->post(
                array_merge($this->url, ['action' => 'add']),
                array_merge($this->example, ['user_id' => $userId])
            );
            $this->assertRedirect(['action' => 'index']);
            $this->assertFlashMessage('The operation has been performed correctly');

            $post = $this->Posts->find()->last();
            $this->assertEquals(3, $post->user_id);

            //Edits record, adding +1 to the `user_id`
            $this->post(
                array_merge($this->url, ['action' => 'edit', $post->id]),
                array_merge($this->example, ['user_id' => $userId + 1])
            );
            $this->assertRedirect(['action' => 'index']);
            $this->assertFlashMessage('The operation has been performed correctly');

            $post = $this->Posts->findById($post->id)->first();
            $this->assertEquals(3, $post->user_id);

            $this->Posts->delete($post);
        }
    }
}
