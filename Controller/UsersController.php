<?php
App::uses('MeCmsBackendAppController', 'MeCmsBackend.Controller');

/**
 * UsersController
 *
 * This file is part of MeCms Backend
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Controller
 */

/**
 * Users Controller
 */
class UsersController extends MeCmsBackendAppController {
	/**
	 * Internal function to logout
	 * @return boolean
	 */
	private function _logout() {
		return $this->redirect($this->Auth->logout());
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		
		//Allows users to logout
		$this->Auth->allow('logout');
	}
	
	/**
	 * List users
	 */
	public function admin_index() {
		$this->paginate = array(
			'contain'	=> 'Group.label',
			'fields'	=> array('id', 'username', 'email', 'full_name', 'active', 'banned', 'post_count', 'created'),
			'limit'		=> $this->config['site']['records_for_page']
		);
		
		$this->set(array(
			'users'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms_backend', 'Users')
		));
	}

	/**
	 * View user
	 * @param string $id User id
	 * @throws NotFoundException
	 */
	public function admin_view($id = NULL) {
		if(!$this->User->exists($id))
			throw new NotFoundException(__d('me_cms_backend', 'Invalid user'));
		
		$user = $this->User->find('first', array(
			'conditions'	=> array('User.id' => $id),
			'contain'		=> 'Group.label',
			'fields'		=> array('id', 'username', 'email', 'full_name', 'banned', 'post_count', 'created')
		));
		
		$this->set(array(
			'user'				=> $user,
			'title_for_layout'	=> __d('me_cms_backend', 'View user')
		));
	}

	/**
	 * Add user
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->User->create();
			if($this->User->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The user has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The user could not be created. Please, try again'), 'error');
		}

		$this->set(array(
			'groups'			=> $this->User->Group->find('list'),
			'title_for_layout'	=> __d('me_cms_backend', 'Add user')
		));
	}

	/**
	 * Edit user
	 * @param string $id User id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		//TO-DO: verificare non si stia modificando utente con ID 1
		if(!$this->User->exists($id))
			throw new NotFoundException(__d('me_cms_backend', 'Invalid user'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			//This prevents a blank password is saved
			if(empty($this->request->data['User']['password']))
				unset($this->request->data['User']['password']);
				
			if($this->User->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The user has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The user could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->User->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'group_id', 'username', 'email', 'first_name', 'last_name', 'active')
			));


		$this->set(array(
			'groups'			=> $this->User->Group->find('list'),
			'title_for_layout'	=> __d('me_cms_backend', 'Edit user')
		));
	}

	/**
	 * Delete user
	 * @param string $id User id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->User->id = $id;
		if(!$this->User->exists())
			throw new NotFoundException(__d('me_cms_backend', 'Invalid user'));
			
		$this->request->onlyAllow('post', 'delete');
		
		//Checks if the user is the admin founder
		if($id == 1)
			$this->Session->flash(__d('me_cms_backend', 'You cannot delete this user, because he\'s the admin founder'), 'error');
		//Checks if the user has many posts
		elseif($this->User->field('post_count'))
			$this->Session->flash(__d('me_cms_backend', 'Before you delete this user, you have to delete his posts or assign them to another user'), 'error');
		//Now we can delete the user...
		else {
			if($this->User->delete())
				$this->Session->flash(__d('me_cms_backend', 'The user has been deleted'));
			else
				$this->Session->flash(__d('me_cms_backend', 'The user was not deleted'), 'error');
		}
		
		$this->redirect(array('action' => 'index'));
	}
	
	/**
	 * Change the user password
	 */
	public function admin_change_password() {
		//Sets the user id
		$this->request->data['User']['id'] = $this->Auth->user('id');
		
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->User->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The password has been changed'));
				$this->redirect('/admin');
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The password has not been changed. Please, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms_backend', 'Change password'));
	}
	
	/**
	 * Login
	 * @return boolean
	 * @uses _logout() to logout the user, if his account has not been activated or if he has been banned
	 * @uses isLogged() to checks if the user is already logged in
	 */
	public function login() {
		//Checks if the user is already logged in
		if($this->isLogged()) {
			$this->Session->flash(__d('me_cms_backend', 'You are already logged in'), 'alert');
			return $this->redirect($this->Auth->redirect());
		}
		
		if($this->request->is('post')) {
			if($this->Auth->login()) {
				//Gets the user data ("active" and "banned" fields)
				$user =	$this->User->find('first', array(
					'conditions'	=> array('User.'.$this->User->primaryKey => $this->Auth->user('id')),
					'fields'		=> array('active', 'banned')
				));
				
				//Checks if the user is banned
				if($user['User']['banned']) {
					$this->Session->flash(__d('me_cms_backend', 'Your account has been banned by an admin'), 'error');
					return $this->_logout();
				}
				//Checks if the user is disabled (the account should still be enabled)
				elseif(!$user['User']['active']) {
					$this->Session->flash(__d('me_cms_backend', 'Your account has not been activated yet'), 'error');
					return $this->_logout();
				}
				
				//Login...
				return $this->redirect($this->Auth->redirect());
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'Invalid username or password'), 'error');
		}
		
		$this->layout = 'login';
	}

	/**
	 * Logout
	 * @return boolean
	 * @uses _logout() to logout the user
	 */
	public function logout() {
		$this->Session->flash(__d('me_cms_backend', 'You are successfully logged out'));
		return $this->_logout();
	}
}