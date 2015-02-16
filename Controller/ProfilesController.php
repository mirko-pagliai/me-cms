<?php
/**
 * ProfilesController
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
 * Profiles Controller
 */
class ProfilesController extends MeCmsAppController {
	/**
	 * Components
	 * @var array
	 */
	public $components = array('MeCms.Email', 'MeTools.Token');
	
	/**
	 * Models
	 * @var array
	 */
	public $uses = array('MeCms.User');
	
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 */
	public function isAuthorized($user = NULL) {
		return TRUE;
	}
	
	/**
	 * Change the user password
	 */
	public function admin_change_password() {
		//Sets the user id
		$this->request->data['User']['id'] = $this->Auth->user('id');
		
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->User->save($this->request->data)) {
				//Gets the user
				$user = $this->User->findById($this->Auth->user('id'), array('email', 'full_name'));

				//Sends email
				$this->Email->to(array($user['User']['email'] => $user['User']['full_name']));
				$this->Email->subject(__d('me_cms', 'Your password has been changed'));
				$this->Email->template('change_password');
				$this->Email->set('full_name', $user['User']['full_name']);
				$this->Email->send();
				
				$this->Session->flash(__d('me_cms', 'The password has been edited'));
				$this->redirect('/admin');
			}
			else
				$this->Session->flash(__d('me_cms', 'The password has not been edited. Please, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Change password'));
	}
	
	/**
	 * Request a new password
	 */
	public function request_new_password() {
		if($this->request->is('post') || $this->request->is('put')) {
			//Gets the user
			$user = $this->User->findByEmail($email = $this->request->data['User']['email'], array('id', 'full_name'));
			
			if(!empty($user)) {
				//Gets the token
				$token = $this->Token->create($email, array('type' => 'newpassword', 'user_id' => $id = $user['User']['id']));
				//Sets the url to reset the password
				$url = Router::url(am(array('controller' => 'profiles', 'action' => 'reset_password'), compact('id', 'token')), TRUE);
				
				//Sends email
				$this->Email->to(array($email => $full_name = $user['User']['full_name']));
				$this->Email->subject(__d('me_cms', 'Reset your password'));
				$this->Email->template('request_new_password');
				$this->Email->set(compact('full_name', 'url'));
				$this->Email->send();
				
				$this->Session->flash(__d('me_cms', 'We have sent you an email to reset your password'));
				$this->redirect('/login');
			}
			else
				$this->Session->flash(__d('me_cms', 'No account found'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Request new password'));
		$this->layout = 'MeCms.users';
	}
}