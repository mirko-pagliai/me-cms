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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use MeCms\Controller\AppController;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController
{
    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\PostsCategoriesTable::getList()
     * @uses MeCms\Model\Table\PostsCategoriesTable::getTreeList()
     * @uses MeCms\Model\Table\UsersTable::getActiveList()
     * @uses MeCms\Model\Table\UsersTable::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['add', 'edit'])) {
            $categories = $this->Posts->Categories->getTreeList();
            $users = $this->Posts->Users->getActiveList();
        } else {
            $categories = $this->Posts->Categories->getList();
            $users = $this->Posts->Users->getList();
        }

        if ($users->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create an user'));

            return $this->redirect(['controller' => 'Users', 'action' => 'index']);
        }

        if ($categories->isEmpty()) {
            $this->Flash->alert(__d('me_cms', 'You must first create a category'));

            return $this->redirect(['controller' => 'PostsCategories', 'action' => 'index']);
        }

        $this->set(compact('categories', 'users'));
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads KcFinderComponent
        if ($this->request->isAction(['add', 'edit'])) {
            $this->loadComponent('MeCms.KcFinder');
        }
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     * @uses MeCms\Model\Table\Traits\IsOwnedByTrait::isOwnedBy()
     */
    public function isAuthorized($user = null)
    {
        //Only admins and managers can edit all posts.
        //Users can edit only their own posts
        if ($this->request->isEdit()) {
            return $this->Auth->isGroup(['admin', 'manager']) ||
                $this->Posts->isOwnedBy($this->request->getParam('pass.0'), $this->Auth->user('id'));
        }

        //Only admins and managers can delete posts
        return $this->request->isDelete() ? $this->Auth->isGroup(['admin', 'manager']) : true;
    }

    /**
     * Lists posts
     * @return void
     * @uses MeCms\Model\Table\PostsTable::queryFromFilter()
     */
    public function index()
    {
        $query = $this->Posts->find()->contain([
            'Categories' => ['fields' => ['id', 'title']],
            'Tags' => function (Query $q) {
                return $q->order(['tag' => 'ASC']);
            },
            'Users' => ['fields' => ['id', 'first_name', 'last_name']],
        ]);

        $this->paginate['order'] = ['created' => 'DESC'];

        $posts = $this->paginate($this->Posts->queryFromFilter($query, $this->request->getQueryParams()));

        $this->set(compact('posts'));
    }

    /**
     * Adds post
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function add()
    {
        $post = $this->Posts->newEntity();

        if ($this->request->is('post')) {
            //Only admins and managers can add posts on behalf of other users
            if (!$this->Auth->isGroup(['admin', 'manager'])) {
                $this->request = $this->request->withData('user_id', $this->Auth->user('id'));
            }

            $post = $this->Posts->patchEntity($post, $this->request->getData());

            if ($this->Posts->save($post)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('post'));
    }

    /**
     * Edits post
     * @param string $id Post ID
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function edit($id = null)
    {
        $post = $this->Posts->findById($id)
            ->contain('Tags', function (Query $q) {
                return $q->order(['tag' => 'ASC']);
            })
            ->formatResults(function (ResultSet $results) {
                return $results->map(function ($row) {
                    $row->created = $row->created->i18nFormat(FORMAT_FOR_MYSQL);

                    return $row;
                });
            })
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            //Only admins and managers can edit posts on behalf of other users
            if (!$this->Auth->isGroup(['admin', 'manager'])) {
                $this->request = $this->request->withData('user_id', $this->Auth->user('id'));
            }

            $post = $this->Posts->patchEntity($post, $this->request->getData());

            if ($this->Posts->save($post)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('post'));
    }
    /**
     * Deletes post
     * @param string $id Post ID
     * @return \Cake\Network\Response|null|void
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $this->Posts->deleteOrFail($this->Posts->get($id));

        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index']);
    }
}
