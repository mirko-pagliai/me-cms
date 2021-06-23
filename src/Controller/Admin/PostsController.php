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

namespace MeCms\Controller\Admin;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\ResultSet;
use MeCms\Controller\Admin\AppController;
use MeCms\Model\Entity\Post;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $Categories
 * @property \MeCms\Model\Table\PostsTable $Posts
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class PostsController extends AppController
{
    /**
     * Called before the controller action.
     *  each controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Model\Table\PostsCategoriesTable::getList()
     * @uses \MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     * @uses \MeCms\Model\Table\UsersTable::getActiveList()
     * @uses \MeCms\Model\Table\UsersTable::getList()
     */
    public function beforeFilter(EventInterface $event)
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        [$categoriesMethod, $usersMethod] = ['getList', 'getList'];
        if ($this->getRequest()->isAction(['add', 'edit'])) {
            [$categoriesMethod, $usersMethod] = ['getTreeList', 'getActiveList'];

            //Only admins and managers can add and edit posts on behalf of other users
            if ($this->getRequest()->getData() && !$this->Auth->isGroup(['admin', 'manager'])) {
                $this->setRequest($this->getRequest()->withData('user_id', $this->Auth->user('id')));
            }
        }
        $users = $this->Users->$usersMethod();
        if ($users->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create an user'));

            return $this->redirect(['controller' => 'Users', 'action' => 'index']);
        }

        $categories = $this->Categories->$categoriesMethod();
        if ($categories->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PostsCategories', 'action' => 'index']);
        }

        $this->set(compact('categories', 'users'));

        return null;
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array|\ArrayAccess|null $user The user to check the authorization
     *  of. If empty the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     * @uses \MeCms\Model\Table\Traits\IsOwnedByTrait::isOwnedBy()
     */
    public function isAuthorized($user = null): bool
    {
        if ($this->Auth->isGroup(['admin', 'manager'])) {
            return true;
        }

        //Users can edit only their own post
        if ($this->getRequest()->isEdit()) {
            [$postId, $userId] = [$this->getRequest()->getParam('pass.0'), $this->Auth->user('id')];

            return $postId && $userId ? $this->Posts->isOwnedBy((int)$postId, $userId) : false;
        }

        //Only admins and managers can delete posts
        return !$this->getRequest()->isDelete();
    }

    /**
     * Lists posts
     * @return void
     * @uses \MeCms\Model\Table\PostsTable::queryFromFilter()
     */
    public function index(): void
    {
        $query = $this->Posts->find()->contain([
            'Categories' => ['fields' => ['id', 'title']],
            'Tags' => ['sort' => ['tag' => 'ASC']],
            'Users' => ['fields' => ['id', 'first_name', 'last_name']],
        ]);

        $this->paginate['order'] = ['created' => 'DESC'];

        $posts = $this->paginate($this->Posts->queryFromFilter($query, $this->getRequest()->getQueryParams()))
            ->map(function (Post $post): Post {
                return $post->set('tags', collection($post->get('tags'))->extract('tag')->toList());
            });

        $this->set(compact('posts'));
    }

    /**
     * Adds post
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function add()
    {
        $post = $this->Posts->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $post = $this->Posts->patchEntity($post, $this->getRequest()->getData());

            if ($this->Posts->save($post)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('post'));
        $this->set('title', __d('me_cms', 'Add post'));
        $this->render('form');
    }

    /**
     * Edits post
     * @param string $id Post ID
     * @return \Cake\Http\Response|null|void
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function edit(string $id)
    {
        $post = $this->Posts->findById($id)
            ->contain(['Tags' => ['sort' => ['tag' => 'ASC']]])
            ->formatResults(function (ResultSet $results) {
                return $results->map(function (Post $post): Post {
                    return $post->set('created', $post->get('created')->i18nFormat(FORMAT_FOR_MYSQL));
                });
            })
            ->firstOrFail();

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $post = $this->Posts->patchEntity($post, $this->getRequest()->getData());

            if ($this->Posts->save($post)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('post'));
        $this->set('title', __d('me_cms', 'Edit post'));
        $this->render('form');
    }

    /**
     * Deletes post
     * @param string $id Post ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $this->Posts->deleteOrFail($this->Posts->get($id));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }
}
