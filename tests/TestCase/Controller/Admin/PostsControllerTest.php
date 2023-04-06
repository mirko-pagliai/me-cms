<?php
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

namespace MeCms\Test\TestCase\Controller\Admin;

use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\Tag;
use MeCms\Model\Entity\User;
use MeCms\Model\Entity\UsersGroup;
use MeCms\TestSuite\Admin\ControllerTestCase;

/**
 * PostsControllerTest class
 * @property \MeCms\Model\Table\PostsTable $Table
 * @group admin-controller
 */
class PostsControllerTest extends ControllerTestCase
{
    /**
     * @var array<string, int|string>
     */
    protected static array $example = [
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'new post title',
        'slug' => 'new-post-slug',
        'text' => 'new post text',
    ];

    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
        'plugin.MeCms.Users',
    ];

    /**
     * @uses \MeCms\Controller\Admin\PostsController::beforeFilter()
     * @test
     */
    public function testBeforeFilter(): void
    {
        $this->Table->Categories->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'PostsCategories', 'action' => 'index']);
            $this->assertFlashMessage('You must first create a category');
        }

        $this->Table->Users->deleteAll(['id IS NOT' => null]);

        foreach (['index', 'add', 'edit'] as $action) {
            $this->get($this->url + compact('action') + [1]);
            $this->assertRedirect(['controller' => 'Users', 'action' => 'index']);
            $this->assertFlashMessage('You must first create an user');
        }
    }

    /**
     * Tests that the admins and managers can add and edit as another user
     * @uses \MeCms\Controller\Admin\PostsController::beforeFilter()
     * @test
     */
    public function testBeforeFilterEditAsAnotherUser(): void
    {
        foreach (['admin', 'manager'] as $userGroup) {
            $this->setAuthData($userGroup);

            foreach ([1, 2] as $userId) {
                //Adds record
                $this->post($this->url + ['action' => 'add'], ['user_id' => $userId] + self::$example);
                $this->assertRedirect(['action' => 'index']);
                $this->assertFlashMessage(I18N_OPERATION_OK);

                $Post = $this->Table->find()->all()->last();
                $this->assertSame($userId, $Post->get('user_id'));

                //Edits record, adding +1 to the `user_id`
                $this->post($this->url + ['action' => 'edit', $Post->get('id')], ['user_id' => ++$userId] + self::$example);
                $this->assertRedirect(['action' => 'index']);
                $this->assertFlashMessage(I18N_OPERATION_OK);

                /** @var \MeCms\Model\Entity\Post $Post */
                $Post = $this->Table->findById($Post->get('id'))->firstOrFail();
                $this->assertSame($userId, $Post->get('user_id'));

                $this->Table->delete($Post);
            }
        }

        /**
         * Tests that the other users cannot add and edit as another user
         */
        $this->setAuthData('user', 3);

        foreach ([1, 2] as $userId) {
            //Adds record
            $this->post($this->url + ['action' => 'add'], ['user_id' => $userId] + self::$example);
            $this->assertRedirect(['action' => 'index']);
            $this->assertFlashMessage(I18N_OPERATION_OK);

            $Post = $this->Table->find()->all()->last();
            $this->assertSame(3, $Post->get('user_id'));

            //Edits record, adding +1 to the `user_id`
            $this->post($this->url + ['action' => 'edit', $Post->get('id')], ['user_id' => ++$userId] + self::$example);
            $this->assertRedirect(['action' => 'index']);
            $this->assertFlashMessage(I18N_OPERATION_OK);

            /** @var \MeCms\Model\Entity\Post $Post */
            $Post = $this->Table->findById($Post->get('id'))->firstOrFail();
            $this->assertSame(3, $Post->get('user_id'));

            $this->Table->delete($Post);
        }
    }

    /**
     * @uses \MeCms\Controller\Admin\PostsController::isAuthorized()
     * @test
     */
    public function testIsAuthorized(): void
    {
        foreach (['add', 'index'] as $action) {
            $this->assertAllGroupsAreAuthorized($action);
        }

        foreach (['edit', 'delete'] as $action) {
            $this->assertOnlyUserIsNotAuthorized($action);
        }

        /**
         * With `edit` action and a user who owns the record.
         * Gets the ID of a post that belongs to user with ID 2
         * @var \MeCms\Model\Entity\Post $Post
         */
        $Post = $this->Table->findByUserId(2)->firstOrFail();
        $Request = $this->Controller->getRequest()->withParam('pass.0', $Post->get('id'))
            ->withParam('action', 'edit');
        $this->Controller->setRequest($Request);
        //User with ID 2 is authorized to edit
        $User = new User(['id' => 2, 'group' => new UsersGroup(['name' => 'user'])]);
        $this->assertTrue($this->Controller->isAuthorized($User));
        //User with ID 1 is not authorized to edit
        $User = new User(['id' => 1, 'group' => new UsersGroup(['name' => 'user'])]);
        $this->assertFalse($this->Controller->isAuthorized($User));
    }

    /**
     * @uses \MeCms\Controller\Admin\PostsController::index()
     * @test
     */
    public function testIndex(): void
    {
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Posts' . DS . 'index.php');
        $this->assertContainsOnlyInstancesOf(Post::class, $this->viewVariable('posts'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PostsController::add()
     * @test
     */
    public function testAdd(): void
    {
        $url = $this->url + ['action' => 'add'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Posts' . DS . 'form.php');
        $this->assertInstanceOf(Post::class, $this->viewVariable('post'));

        //POST request. Data are valid
        $this->post($url, self::$example);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(Post::class, $this->viewVariable('post'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PostsController::edit()
     * @test
     */
    public function testEdit(): void
    {
        $url = $this->url + ['action' => 'edit', 1];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Posts' . DS . 'form.php');
        $this->assertInstanceOf(Post::class, $this->viewVariable('post'));
        $this->assertContainsOnlyInstancesOf(Tag::class, $this->viewVariable('post')->get('tags'));

        //POST request. Data are valid
        $this->post($url, ['title' => 'another title']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);

        //POST request. Data are invalid
        $this->post($url, ['title' => 'aa']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains(I18N_OPERATION_NOT_OK);
        $this->assertInstanceOf(Post::class, $this->viewVariable('post'));
    }

    /**
     * @uses \MeCms\Controller\Admin\PostsController::delete()
     * @test
     */
    public function testDelete(): void
    {
        $this->post($this->url + ['action' => 'delete', 1]);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertTrue($this->Table->findById(1)->all()->isEmpty());
    }
}
