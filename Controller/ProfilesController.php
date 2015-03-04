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
		if($this->request->is('post') || $this->request->is('put')) {
			$this->User->id = $this->Auth->user('id');
			
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
	 * Activate account.
	 * @param string $id User ID
	 * @param string $token Token
	 * @throws NotFoundException
	 */
	public function activate_account($user_id = NULL, $token = NULL) {
		//Redirects if the user is already logged in
		$this->redirectIfLogged();
		
		if(empty($user_id) || empty($token))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		//Checks if the tokens exists and if it'is valid
		if(!$this->Token->check($token, am(array('type' => 'signup'), compact('user_id')))) {
			$this->Session->flash(__d('me_cms', 'Invalid token'), 'error');
			$this->redirect($this->Auth->loginAction);
		}
		
		$this->User->id = $user_id;
		
		if($this->User->saveField('active', 1)) {
			//Deletes the token
			$this->Token->delete($token);

			$this->Session->flash(__d('me_cms', 'The account has been activated. Now you can login'));
		}
		else
			$this->Session->flash(__d('me_cms', 'The account has not been activated. Please, try again'), 'error');
		
		$this->redirect($this->Auth->loginAction);
	}
	
	/**
	 * Requests a new password.
	 * @uses config
	 * @uses redirectIfLogged()
	 * @uses RecaptchaComponent::check()
	 * @uses RecaptchaComponent::getError()
	 * @uses TokenComponent::create()
	 */
	public function forgot_password() {
		//Redirects if the user is already logged in
		$this->redirectIfLogged();
		
		//Checks if reset password is enabled
		if(!$this->config['users']['reset_password']) {
			$this->Session->flash(__d('me_cms', 'Disabled'), 'error');
			$this->redirect($this->Auth->loginAction);
		}
		
		if($this->request->is('post') || $this->request->is('put')) {
			//Sets data
			$this->User->set($this->request->data);
			//Email should not be unique, removes validation rule
			unset($this->User->validate['email']['isUnique']);
			
			//Checks for reCAPTCHA, if requested
			if($this->config['security']['recaptcha'] && !$this->Recaptcha->check()) {
				$this->Session->flash($this->Recaptcha->getError(), 'error');
			}
			elseif($this->User->validates()) {
				//Gets the user
				$user = $this->User->find('active', array(
					'conditions'	=> array('email' => $email = $this->request->data['User']['email']),
					'fields'		=> array('id', 'full_name'),
					'limit'			=> 1
				));
			
				if(!empty($user)) {
					//Gets the token and the url to reset the password
					$token = $this->Token->create($email, array('type' => 'newpassword', 'user_id' => $id = $user['User']['id']));
					$url = Router::url(am(array('controller' => 'profiles', 'action' => 'reset_password'), compact('id', 'token')), TRUE);

					//Sends email
					$this->Email->to(array($email => $full_name = $user['User']['full_name']));
					$this->Email->subject(__d('me_cms', 'Reset your password'));
					$this->Email->template('forgot_password');
					$this->Email->set(compact('full_name', 'url'));
					$this->Email->send();

					$this->Session->flash(__d('me_cms', 'We have sent you an email to reset your password'));
					$this->redirect($this->Auth->loginAction);
				}
				else
					$this->Session->flash(__d('me_cms', 'No account found'), 'error');
			}
			else
				$this->Session->flash(__d('me_cms', 'The form has not been filled in correctly, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Forgot your password?'));
		$this->layout = 'MeCms.users';
	}
	
	/**
	 * Resends the activation mail.
	 * @uses config
	 * @uses redirectIfLogged()
	 * @uses RecaptchaComponent::check()
	 * @uses RecaptchaComponent::getError()
	 * @uses TokenComponent::create()
	 */
	public function resend_activation() {
		//Redirects if the user is already logged in
		$this->redirectIfLogged();
		
		//Checks if signup is enabled and if accounts will be enabled by the user via email
		if(!$this->config['users']['signup'] && $this->config['users']['activation'] === 1) {
			$this->Session->flash(__d('me_cms', 'Disabled'), 'error');
			$this->redirect($this->Auth->loginAction);
		}
		
		if($this->request->is('post') || $this->request->is('put')) {
			//Sets data
			$this->User->set($this->request->data);
			//Email should not be unique, removes validation rule
			unset($this->User->validate['email']['isUnique']);
			
			//Checks for reCAPTCHA, if requested
			if($this->config['security']['recaptcha'] && !$this->Recaptcha->check()) {
				$this->Session->flash($this->Recaptcha->getError(), 'error');
			}
			elseif($this->User->validates()) {
				//Gets the user
				$user = $this->User->find('pending', array(
					'conditions'	=> array('email' => $email = $this->request->data['User']['email']),
					'fields'		=> array('id', 'email', 'full_name'),
					'limit'			=> 1
				));
			
				if(!empty($user)) {
					//Gets the token and the url to activate account
					$token = $this->Token->create($email = $user['User']['email'], array('type' => 'signup', 'user_id' => $id = $user['User']['id']));
					$url = Router::url(am(array('controller' => 'profiles', 'action' => 'activate_account'), compact('id', 'token')), TRUE);

					//Sends email
					$this->Email->to(array($email => $full_name = $user['User']['full_name']));
					$this->Email->subject(__d('me_cms', 'Activate your account'));
					$this->Email->template('signup');
					$this->Email->set(compact('full_name', 'url'));
					$this->Email->send();

					$this->Session->flash(__d('me_cms', 'We send you an email to activate your account'));
					$this->redirect($this->Auth->loginAction);
				}
				else
					$this->Session->flash(__d('me_cms', 'No account found'), 'error');
			}
			else
				$this->Session->flash(__d('me_cms', 'The form has not been filled in correctly, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Resend activation email'));
		$this->layout = 'MeCms.users';
	}
	
	/**
	 * Resets password.
	 * @param string $id User ID
	 * @param string $token Token
	 * @throws NotFoundException
	 * @uses redirectIfLogged()
	 * @uses TokenComponent::check()
	 * @uses TokenComponent::delete()
	 */
	public function reset_password($user_id = NULL, $token = NULL) {
		//Redirects if the user is already logged in
		$this->redirectIfLogged();
		
		if(empty($user_id) || empty($token))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		//Checks if the tokens exists and if it'is valid
		if(!$this->Token->check($token, am(array('type' => 'newpassword'), compact('user_id')))) {
			$this->Session->flash(__d('me_cms', 'Invalid token'), 'error');
			$this->redirect($this->Auth->loginAction);
		}
		
		if($this->request->is('post') || $this->request->is('put')) {
			$this->User->id = $user_id;
			
			if($this->User->save($this->request->data)) {
				//Deletes the token
				$this->Token->delete($token);
				
				$this->Session->flash(__d('me_cms', 'The password has been edited'));
				$this->redirect($this->Auth->loginAction);
			}
			else
				$this->Session->flash(__d('me_cms', 'The password has not been edited. Please, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Reset password'));
		$this->layout = 'MeCms.users';
	}
	
	/**
	 * Sign up.
	 * @uses config
	 * @uses redirectIfLogged()
	 * @uses RecaptchaComponent::check()
	 * @uses RecaptchaComponent::getError()
	 * @uses TokenComponent::create()
	 */
	public function signup() {
		//Redirects if the user is already logged in
		$this->redirectIfLogged();
		
		//Checks if signup is enabled
		if(!$this->config['users']['signup']) {
			$this->Session->flash(__d('me_cms', 'Disabled'), 'error');
			$this->redirect($this->Auth->loginAction);
		}
		
		if($this->request->is('post') || $this->request->is('put')) {
			//Sets default values
			$this->request->data['User'] = am(array(
				'group_id'	=> $this->config['users']['default_group'],
				'active'	=> $this->config['users']['activation'] > 0 ? 0 : 1
			), $this->request->data['User']);
			
			//Checks for reCAPTCHA, if requested
			if($this->config['security']['recaptcha'] && !$this->Recaptcha->check()) {
				$this->Session->flash($this->Recaptcha->getError(), 'error');
			}
			elseif($user = $this->User->save($this->request->data)) {
				switch($this->config['users']['activation']) {
					//The account will be enabled by an administrator
					case 2:
						$this->Session->flash(__d('me_cms', 'The account has been created, but it needs to be activated by an admin'));
						break;
					//The account will be enabled by the user via email (default)
					case 1:
						//Gets the token and the url to activate account
						$token = $this->Token->create($email = $user['User']['email'], array('type' => 'signup', 'user_id' => $id = $user['User']['id']));
						$url = Router::url(am(array('controller' => 'profiles', 'action' => 'activate_account'), compact('id', 'token')), TRUE);
												
						//Sends email
						$this->Email->to(array($email => $full_name = sprintf('%s %s', $user['User']['first_name'], $user['User']['last_name'])));
						$this->Email->subject(__d('me_cms', 'Activate your account'));
						$this->Email->template('signup');
						$this->Email->set(compact('full_name', 'url'));
						$this->Email->send();
						
						$this->Session->flash(__d('me_cms', 'We send you an email to activate your account'));
						break;
					//No activation required, the account is immediately active
					default:
						$this->Session->flash(__d('me_cms', 'Account created. Now you can login'));
						break;
				}
				
				$this->redirect($this->Auth->loginAction);
			}
			else
				$this->Session->flash(__d('me_cms', 'The account has not been created. Please, try again'), 'error');
		}
		
		$this->set('title_for_layout', __d('me_cms', 'Sign up'));
		$this->layout = 'MeCms.users';
	}
}