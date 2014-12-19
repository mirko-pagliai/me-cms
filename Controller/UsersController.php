<?php
/**
 * UsersController
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');

/**
 * Users Controller
 */
class UsersController extends MeCmsAppController {
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses isAction()
	 * @uses MeAuthComponenet::isAdmin()
	 * @uses MeAuthComponenet::isManager()
	 */
	public function isAuthorized($user = NULL) {
		//Everyone can change their own password
		if($this->isAction('admin_change_password'))
			return TRUE;
		
		//Only admins can delete users
		if($this->isAction('admin_delete'))
			return $this->Auth->isAdmin();
		
		//Only admins and managers can access every action
		return $this->Auth->isManager();
	}
	
	/**
	 * Internal function to logout
	 * @return boolean
	 */
	private function _logout() {
		//Deletes all KCFinder keys
		$this->Session->delete('KCFINDER');
		
		return $this->redirect($this->Auth->logout());
	}
	
	/**
	 * List users
	 */
	public function admin_index() {
		$this->paginate = array(
			'contain'	=> 'Group.label',
			'fields'	=> array('id', 'username', 'email', 'full_name', 'active', 'banned', 'post_count', 'created'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'users'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Users')
		));
	}

	/**
	 * View user
	 * @param string $id User id
	 * @throws NotFoundException
	 */
	public function admin_view($id = NULL) {
		if(!$this->User->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		$user = $this->User->find('first', array(
			'conditions'	=> array('User.id' => $id),
			'contain'		=> 'Group.label',
			'fields'		=> array('id', 'username', 'email', 'full_name', 'active', 'banned', 'post_count', 'created')
		));
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'View user')), compact('user')));
	}

	/**
	 * Add user
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->User->create();
			if($this->User->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The user has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The user could not be created. Please, try again'), 'error');
		}

		$this->set(array(
			'groups'			=> $this->User->Group->find('list'),
			'title_for_layout'	=> __d('me_cms', 'Add user')
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
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			//This prevents a blank password is saved
			if(empty($this->request->data['User']['password']))
				unset($this->request->data['User']['password']);
				
			if($this->User->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The user has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The user could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->User->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'group_id', 'username', 'email', 'first_name', 'last_name', 'active')
			));


		$this->set(array(
			'groups'			=> $this->User->Group->find('list'),
			'title_for_layout'	=> __d('me_cms', 'Edit user')
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
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		$this->request->onlyAllow('post', 'delete');
				
		//Before deleting, it checks if the user is a admin found or if the user has some posts
		if($id > 1 && !$this->User->field('post_count')) {
			if($this->User->delete())
				$this->Session->flash(__d('me_cms', 'The user has been deleted'));
			else
				$this->Session->flash(__d('me_cms', 'The user was not deleted'), 'error');
		}
		elseif($id == 1)
			$this->Session->flash(__d('me_cms', 'You cannot delete this user, because he\'s the admin founder'), 'error');
		else
			$this->Session->flash(__d('me_cms', 'Before you delete this user, you have to delete his posts or assign them to another user'), 'error');
		
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
				$this->Session->flash(__d('me_cms', 'The password has been edited'));
				$this->redirect('/admin');
			}
			else
				$this->Session->flash(__d('me_cms', 'The password has not been edited. Please, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Change password'));
	}
	
	/**
	 * Login
	 * @return boolean
	 * @uses _logout() to logout the user, if his account has not been activated or if he has been banned
	 * @uses MeAuthComponent::isLogged() to checks if the user is already logged in
	 */
	public function login() {
		//Checks if the user is already logged in
		if($this->Auth->isLogged()) {
			$this->Session->flash(__d('me_cms', 'You are already logged in'), 'alert');
			return $this->redirect($this->Auth->redirect());
		}
		
		if($this->request->is('post')) {
			if($this->Auth->login()) {
				//Gets the user data ("active" and "banned" fields)
				$user =	$this->User->find('first', array(
					'conditions'	=> array('id' => $this->Auth->user('id')),
					'fields'		=> array('active', 'banned')
				));
				
				//Checks if the user is banned
				if($user['User']['banned']) {
					$this->Session->flash(__d('me_cms', 'Your account has been banned by an admin'), 'error');
					return $this->_logout();
				}
				//Checks if the user is disabled (the account should still be enabled)
				elseif(!$user['User']['active']) {
					$this->Session->flash(__d('me_cms', 'Your account has not been activated yet'), 'error');
					return $this->_logout();
				}
				
				//Login...
				return $this->redirect($this->Auth->redirect());
			}
			else
				$this->Session->flash(__d('me_cms', 'Invalid username or password'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Login'));
		$this->layout = 'MeCms.users';
	}

	/**
	 * Logout
	 * @return boolean
	 * @uses _logout() to logout the user
	 */
	public function logout() {
		$this->Session->flash(__d('me_cms', 'You are successfully logged out'));
		return $this->_logout();
	}
}