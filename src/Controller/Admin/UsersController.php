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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use Cake\Mailer\MailerAwareTrait;
use MeCms\Controller\AppController;
use MeCms\Utility\LoginLogger;

/**
 * Users controller
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController {
	use MailerAwareTrait;
	
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Every user can change his password
		if($this->request->isAction('change_password')) {
			return TRUE;
        }
		
		//Only admins can activate account and delete users
		if($this->request->isAction(['activate', 'delete'])) {
			return $this->Auth->isGroup('admin');
        }
		
		//Admins and managers can access other actions
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeCms\Model\Table\UsersGroupsTable::getList()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		if($this->request->isAction(['index', 'add', 'edit'])) {
			$this->set('groups', $this->Users->Groups->getList());
        }
	}
	
	/**
     * Lists users
	 * @uses MeCms\Model\Table\UsersTable::queryFromFilter()
     */
    public function index() {
		$query = $this->Users->find()
			->select(['id', 'username', 'email', 'first_name', 'last_name', 'active', 'banned', 'post_count', 'created'])
			->contain([
                'Groups' => function($q) {
                    return $q->select(['id', 'label']);
                },
            ]);
		
		$this->paginate['order'] = ['Users.username' => 'ASC'];
		$this->paginate['sortWhitelist'] = ['Users.username', 'first_name', 'email', 'Groups.label', 'post_count', 'created'];
		
        $users = $this->paginate($this->Users->queryFromFilter($query, $this->request->query));
        
		$this->set(compact('users'));
    }
	
    /**
     * Views user
     * @param string $id User ID
     * @uses MeCms\Utility\LoginLogger::get()
     */
    public function view($id = NULL) {
        $user = $this->Users->find()
			->select(['id', 'username', 'email', 'first_name', 'last_name', 'active', 'banned', 'post_count', 'created'])
			->contain(['Groups' => ['fields' => ['label']]])
			->where(['Users.id' => $id])
			->firstOrFail();
        
        $this->set(am([
            'loginLog' => (new LoginLogger($id))->get(),
        ], compact('user')));
    }

    /**
     * Adds user
     */
    public function add() {
        $user = $this->Users->newEntity();
		
        if($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
			
            if($this->Users->save($user)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
				return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Edits user
     * @param string $id User ID
	 * @uses MeCms\Controller\Component\AuthComponent::isFounder()
     */
    public function edit($id = NULL)  {
		$user = $this->Users->find()
			->select(['id', 'group_id', 'username', 'email', 'first_name', 'last_name', 'active'])
			->where(['Users.id' => $id])
			->firstOrFail();
		
		//Only the admin founder can edit others admin users
		if($user->group_id === 1 && !$this->Auth->isFounder()) {
			$this->Flash->alert(__d('me_cms', 'Only the admin founder can do this'));
			return $this->redirect(['action' => 'index']);
		}
		
		//It prevents a blank password is saved
		if(!$this->request->data('password')) {
			unset($this->request->data['password'], $this->request->data['password_repeat']);
        }
        
		$user = $this->Users->patchEntity($user, $this->request->data, ['validate' => 'EmptyPassword']);
			
        if($this->request->is(['patch', 'post', 'put'])) {
            if($this->Users->save($user)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
                return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('user'));
    }
    /**
     * Deletes user
     * @param string $id User ID
	 * @uses MeCms\Controller\Component\AuthComponent::isFounder()
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
		$user = $this->Users->find()
			->select(['id', 'group_id', 'post_count'])
			->where(['Users.id' => $id])
			->firstOrFail();
		
		//You cannot delete the admin founder
		if($user->id === 1) {
			$this->Flash->error(__d('me_cms', 'You cannot delete the admin founder'));
        }
		//Only the admin founder can delete others admin users
		elseif($user->group_id === 1 && !$this->Auth->isFounder()) {
			$this->Flash->alert(__d('me_cms', 'Only the admin founder can do this'));
        }
		elseif(!empty($user->post_count)) {
			$this->Flash->alert(__d('me_cms', 'Before deleting this, you must delete or reassign all items that belong to this element'));
        }
		else {
	        if($this->Users->delete($user)) {
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
            }
	        else  {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
		}
		
        return $this->redirect(['action' => 'index']);
    }
	
	/**
	 * Activates account
	 * @param string $id User ID
	 */
	public function activate($id) {
		$user = $this->Users->get($id);
		
		$user->active = TRUE;
		
		if($this->Users->save($user)) {
			$this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        }
		else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }
        
        return $this->redirect(['action' => 'index']);
	}
	
	/**
	 * Changes the user's password
	 * @uses MeCms\Mailer\UserMailer::change_password()
	 */
	public function change_password() {
		$user = $this->Users->find()
			->select(['id', 'email', 'first_name', 'last_name'])
			->where(['id' => $this->Auth->user('id')])
			->firstOrFail();
		
        if($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->data);
			
			if($this->Users->save($user)) {
				//Sends email
				$this->getMailer('MeCms.User')->send('change_password', [$user]);
				
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
				return $this->redirect(['_name' => 'dashboard']);
			}
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
		}

		$this->set(compact('user'));
	}
}