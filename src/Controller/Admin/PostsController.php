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

use Cake\Collection\CollectionInterface;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\ResultSet;
use MeCms\Model\Entity\Post;
use MeCms\Model\Entity\User;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        $result = parent::beforeFilter($event);
        if ($result) {
            return $result;
        }

        [$categoriesMethod, $usersMethod] = ['getList', 'getList'];
        if ($this->getRequest()->is('action', ['add', 'edit'])) {
            [$categoriesMethod, $usersMethod] = ['getTreeList', 'getActiveList'];

            //Only admins and managers can add and edit posts on behalf of other users
            if ($this->getRequest()->getData() && !in_array($this->Authentication->getIdentityData('group.name'), ['admin', 'manager'])) {
                $this->setRequest($this->getRequest()->withData('user_id', $this->Authentication->getIdentityData('id')));
            }
        }
        $users = $this->Posts->Users->$usersMethod()->all();
        if ($users->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create an user'));

            return $this->redirect(['controller' => 'Users', 'action' => 'index']);
        }

        $categories = $this->Posts->Categories->$categoriesMethod()->all();
        if ($categories->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PostsCategories', 'action' => 'index']);
        }

        $this->set(compact('categories', 'users'));

        return null;
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Model\Table\Traits\IsOwnedByTrait::isOwnedBy()
     */
    public function isAuthorized(User $User): bool
    {
        //By default, administrators and managers are authorized
        if (in_array($User->get('group')->get('name'), ['admin', 'manager'])) {
            return true;
        }

        //Simple users can edit only their own post
        if ($this->getRequest()->is('action', 'edit')) {
            [$postId, $userId] = [$this->getRequest()->getParam('pass.0'), $User->get('id')];

            return $postId && $userId && $this->Posts->isOwnedBy((int)$postId, $userId);
        }

        return !$this->getRequest()->is('action', 'delete');
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
            ->map(fn(Post $post): Post => $post->set('tags', collection($post->get('tags'))->extract('tag')->toList()));

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
     */
    public function edit(string $id)
    {
        $post = $this->Posts->findById($id)
            ->contain(['Tags' => ['sort' => ['tag' => 'ASC']]])
            ->formatResults(fn(ResultSet $results): CollectionInterface => $results->map(fn(Post $post): Post => $post->set('created', $post->get('created')->i18nFormat(FORMAT_FOR_MYSQL))))
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
