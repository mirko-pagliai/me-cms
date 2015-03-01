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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
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
	 * Components
	 * @var array
	 */
	public $components = array('Cookie');
	
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponent::isAdmin()
	 * @uses MeAuthComponent::isManager()
	 * @uses MeToolsAppController::isAction()
	 */
	public function isAuthorized($user = NULL) {		
		//Only admins can activate account and delete users
		if($this->isAction(array('admin_activate_account', 'admin_delete')))
			return $this->Auth->isAdmin();
		
		//Only admins and managers can access every action
		return $this->Auth->isManager();
	}
	
	/**
	 * Called before the controller action. 
	 * It's used to perform logic before each controller action.
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		
		//Sets the cookies expiration
		$this->Cookie->time = '365 days';
	}
	
	/**
	 * Internal function to login with cookie
	 * @return mixed
	 * @uses _logout()
	 */
	private function _loginWithCookie() {
		//Checks if the cookie exists
		if(!$this->Cookie->check('User'))
			return FALSE;
		
		//Gets the user data
		$user = $this->User->find('active', array('conditions' => $this->Cookie->read('User'), 'limit' => 1));
		
		//Unsets the user password
		unset($user['password']);
		
		//If the user data are wrong and the user cannot login
		if(empty($user) || $this->Auth->login($user['User']))
			return $this->_logout();
			
		return $this->redirect($this->Auth->redirect());
	}
	
	/**
	 * Internal function to logout.
	 * @return mixed
	 */
	private function _logout() {		
		//Deletes the login cookie
		$this->Cookie->delete('User');
		
		//Deletes the KCFinder session
		$this->Session->delete('KCFINDER');
		
		//Deletes JS cookie
		setcookie('sidebar-lastmenu', '', 1, '/');
		
		return $this->redirect($this->Auth->logout());
	}
	
	/**
	 * Activate account.
	 * @param string $id User ID
	 * @throws NotFoundException
	 */
	public function admin_activate_account($id = NULL) {
		$this->User->id = $id;
		if(!$this->User->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		if($this->User->saveField('active', 1))
			$this->Session->flash(__d('me_cms', 'The account has been activated'));
		else
			$this->Session->flash(__d('me_cms', 'The account has not been activated. Please, try again'), 'error');
		
		$this->redirect(array('action' => 'index'));
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
	 * Delete user
	 * @param string $id User id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->User->id = $id;
		if(!$this->User->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		$this->request->onlyAllow('post', 'delete');
		
		//You cannot delete the admin founder
		if($this->User->isFounder($id))
			$this->Session->flash(__d('me_cms', 'You cannot delete the admin founder'), 'alert');
		//Only the admin founder can delete others admin users
		elseif($this->User->isAdmin($id) && !$this->Auth->isFounder())
			$this->Session->flash(__d('me_cms', 'Only the founder admin can delete other admin users'), 'alert');
		//Before deleting, checks if the user has some posts
		elseif($this->User->field('post_count'))
			$this->Session->flash(__d('me_cms', 'Before you delete this user, you have to delete his posts or assign them to another user'), 'alert');
		else {
			if($this->User->delete())
				$this->Session->flash(__d('me_cms', 'The user has been deleted'));
			else
				$this->Session->flash(__d('me_cms', 'The user was not deleted'), 'error');
		}
		
		$this->redirect(array('action' => 'index'));
	}

	/**
	 * Edit user
	 * @param string $id User id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->User->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));

		//Only the admin founder can edit others admin users
		if($this->User->isAdmin($id) && !$this->Auth->isFounder()) {
			$this->Session->flash(__d('me_cms', 'Only the founder admin can edit other admin users'), 'alert');
			$this->redirect(array('action' => 'index'));
		}
		
		//The password can be empty
		$this->User->validate['password']['minLength']['allowEmpty'] = TRUE;
		$this->User->validate['password_repeat']['allowEmpty'] = TRUE;
		
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
			$this->request->data = $this->User->findById($id, array('id', 'group_id', 'username', 'email', 'first_name', 'last_name', 'active'));


		$this->set(array(
			'groups'			=> $this->User->Group->find('list'),
			'title_for_layout'	=> __d('me_cms', 'Edit user')
		));
	}
	
	/**
	 * List users
	 * @uses User::conditionsFromFilter()
	 */
	public function admin_index() {
		//Sets conditions from the filter form
		$conditions = empty($this->request->query) ? array() : $this->User->conditionsFromFilter($this->request->query);
		
		$this->paginate = am(array(
			'contain'	=> 'Group.label',
			'fields'	=> array('id', 'username', 'email', 'full_name', 'active', 'banned', 'post_count', 'created'),
			'limit'		=> $this->config['backend']['records'],
			'order'		=> array('User.username' => 'ASC')
		), compact('conditions'));
		
		$this->set(array(
			'groups'			=> $this->User->Group->find('list'),
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
	 * Login
	 * @return boolean
	 * @uses _loginWithCookie()
	 * @uses _logout()
	 * @uses redirectIfLogged()
	 */
	public function login() {
		//Redirects if the user is already logged in
		$this->redirectIfLogged();
		
		//Tries to login with cookies, if the login with cookies is enabled
		if($this->config['users']['cookies_login'])
			$this->_loginWithCookie();
		
		if($this->request->is('post')) {
			if($this->Auth->login()) {
				//Gets the user
				$user =	$this->User->findById($this->Auth->user('id'), array('password', 'active', 'banned'));
				
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
				
				//Saves the login data in a cookie, if it was requested
				if(!empty($this->request->data['User']['remember_me']))
					$this->Cookie->write('User', array(
						'username' => $this->request->data['User']['username'], 
						'password' => $user['User']['password']
					));
				
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