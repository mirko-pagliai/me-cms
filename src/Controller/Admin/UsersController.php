<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\Mailer\MailerAwareTrait;
use MeCms\Controller\AppController;

/**
 * Users controller
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::beforeFilter()
     * @uses MeCms\Model\Table\UsersGroupsTable::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['index', 'add', 'edit'])) {
            $groups = $this->Users->Groups->getList();

            if (!$groups) {
                $this->Flash->alert(__d('me_cms', 'You must first create an user group'));

                return $this->redirect(['controller' => 'UsersGroups', 'action' => 'index']);
            }

            $this->set(compact('groups'));
        }
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads components
        $this->loadComponent('MeCms.LoginRecorder');
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Every user can change his password
        if ($this->request->isAction('changePassword')) {
            return true;
        }

        //Only admins can activate account and delete users
        if ($this->request->isAction(['activate', 'delete'])) {
            return $this->Auth->isGroup('admin');
        }

        //Admins and managers can access other actions
        return $this->Auth->isGroup(['admin', 'manager']);
    }

    /**
     * Lists users
     * @return void
     * @uses MeCms\Model\Table\UsersTable::queryFromFilter()
     */
    public function index()
    {
        $query = $this->Users->find()->contain(['Groups' => ['fields' => ['id', 'label']]]);

        $this->paginate['order'] = ['username' => 'ASC'];

        $users = $this->paginate($this->Users->queryFromFilter($query, $this->request->getQuery()));

        $this->set(compact('users'));
    }

    /**
     * Views user
     * @param string $id User ID
     * @return void
     * @uses MeCms\Controller\Component\LoginRecorderComponent::read()
     */
    public function view($id = null)
    {
        $user = $this->Users->find()
            ->select(['id', 'username', 'email', 'first_name', 'last_name', 'active', 'banned', 'post_count', 'created'])
            ->contain(['Groups' => ['fields' => ['label']]])
            ->where(['Users.id' => $id])
            ->firstOrFail();

        $this->set(compact('user'));

        if (config('users.login_log')) {
            $loginLog = $this->LoginRecorder->config('user', $id)->read();

            $this->set(compact('loginLog'));
        }
    }

    /**
     * Adds user
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Edits user
     * @param string $id User ID
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\AuthComponent::isFounder()
     */
    public function edit($id = null)
    {
        $user = $this->Users->find()
            ->select(['id', 'group_id', 'username', 'email', 'first_name', 'last_name', 'active'])
            ->where(['Users.id' => $id])
            ->firstOrFail();

        //Only the admin founder can edit others admin users
        if ($user->group_id === 1 && !$this->Auth->isFounder()) {
            $this->Flash->alert(__d('me_cms', 'Only the admin founder can do this'));

            return $this->redirect(['action' => 'index']);
        }

        $user = $this->Users->patchEntity($user, $this->request->getData(), ['validate' => 'EmptyPassword']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->Users->save($user)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('user'));
    }
    /**
     * Deletes user
     * @param string $id User ID
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\AuthComponent::isFounder()
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $user = $this->Users->find()
            ->select(['id', 'group_id', 'post_count'])
            ->where(['Users.id' => $id])
            ->firstOrFail();

        //You cannot delete the admin founder
        if ($user->id === 1) {
            $this->Flash->error(__d('me_cms', 'You cannot delete the admin founder'));
        //Only the admin founder can delete others admin users
        } elseif ($user->group_id === 1 && !$this->Auth->isFounder()) {
            $this->Flash->alert(__d('me_cms', 'Only the admin founder can do this'));
        } elseif (!empty($user->post_count)) {
            $this->Flash->alert(__d(
                'me_cms',
                'Before deleting this, you must delete or reassign all items that belong to this element'
            ));
        } else {
            if ($this->Users->delete($user)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Activates account
     * @param string $id User ID
     * @return \Cake\Network\Response|null
     */
    public function activate($id)
    {
        $user = $this->Users->get($id);

        $user->active = true;

        if ($this->Users->save($user)) {
            $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        } else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Changes the user's password
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Mailer\UserMailer::changePassword()
     */
    public function changePassword()
    {
        $user = $this->Users->find()
            ->select(['id', 'email', 'first_name', 'last_name'])
            ->where(['id' => $this->Auth->user('id')])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                //Sends email
                $this->getMailer('MeCms.User')->send('changePassword', [$user]);

                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));

                return $this->redirect(['_name' => 'dashboard']);
            } else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Displays the login log
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\LoginRecorderComponent::read()
     */
    public function lastLogin()
    {
        //Checks if login logs are enabled
        if (!config('users.login_log')) {
            $this->Flash->error(__d('me_cms', 'Disabled'));

            return $this->redirect(['_name' => 'admin']);
        }

        $loginLog = $this->LoginRecorder->config('user', $this->Auth->user('id'))->read();

        $this->set(compact('loginLog'));
    }
}
