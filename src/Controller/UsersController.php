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
namespace MeCms\Controller;

use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\Router;
use MeCms\Controller\AppController;

/**
 * Users controller
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController {
	use MailerAwareTrait;
	
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		//Checks if the user is already logged in
		if($this->request->isAction(['activate_account', 'forgot_password', 'login', 'resend_activation', 'reset_password', 'signup']))
			if($this->Auth->isLogged())
				$this->redirect(['_name' => 'dashboard']);
		
		//See http://book.cakephp.org/2.0/en/core-libraries/components/security-component.html#disabling-csrf-and-post-data-validation-for-specific-actions
		$this->Security->config('unlockedActions', ['forgot_password', 'resend_activation', 'signup']);
	}
	
	/**
	 * Internal function to login with cookie
	 * @return mixed
	 * @uses _logout()
	 */
	private function _loginWithCookie() {
		//Checks if the cookie exists
		if(!$this->Cookie->read('login'))
			return;
		
		$this->request->data = $this->Cookie->read('login');
		
		//Tries to login...
		if(!empty($this->request->data['username']) && !empty($this->request->data['password']))
			if(($user = $this->Auth->identify()) && $user['active'] && !$user['banned']) {
				$this->Auth->setUser($user);
				return $this->redirect($this->Auth->redirectUrl());
			}
		
		//Internal function to logout
		$this->_logout();
	}
	
	/**
	 * Internal function to logout
	 * @return mixed
	 */
	private function _logout() {
		//Deletes the login cookie
		$this->Cookie->delete('login');
		
		//Deletes the KCFinder session
		$this->request->session()->delete('KCFINDER');
		
		//Deletes JS cookie
		setcookie('sidebar-lastmenu', '', 1, '/');
		
		return $this->redirect($this->Auth->logout());
	}
	
	/**
	 * Initialization hook method
	 */
	public function initialize() {
		parent::initialize();
		
        $this->loadComponent('Cookie');
        $this->loadComponent('MeTools.Token');
	}
	
	/**
	 * Activates account
	 * @param string $id User ID
	 * @param string $token Token
	 * @uses MeTools\Controller\Component\Token::check()
	 * @uses MeTools\Controller\Component\Token::delete()
     * @throws NotFoundException
	 */
	public function activate_account($id, $token) {
		//Checks for token
		if(!$this->Token->check($token, ['type' => 'signup', 'user_id' => $id])) {
			$this->Flash->error(__d('me_cms', 'Invalid token'));
			$this->redirect(['_name' => 'login']);
		}
		
		$user = $this->Users->find('pending')
			->select(['id'])
			->where(compact('id'))
			->first();
		
		if($user->isEmpty())
			throw new NotFoundException(__d('me_cms', 'No account found'));
		
		$user->active = TRUE;
				
		if($this->Users->save($user)) {
			//Deletes the token
			$this->Token->delete($token);
			
			$this->Flash->success(__d('me_cms', 'The account has been activated'));
		}
		else
			$this->Flash->error(__d('me_cms', 'The account has not been activated'));
		
        return $this->redirect(['_name' => 'login']);
	}
	
	/**
	 * Requests a new password
	 * @uses MeCms\Mailer\UserMailer::forgot_password()
	 * @uses MeTools\Controller\Component\Recaptcha::check()
	 * @uses MeTools\Controller\Component\Recaptcha::getError()
	 * @uses MeTools\Controller\Component\Token::create()
	 */
	public function forgot_password() {
		//Checks if reset password is enabled
		if(!config('users.reset_password')) {
			$this->Flash->error(__d('me_cms', 'Disabled'));
			$this->redirect(['_name' => 'homepage']);
		}
		
		if($this->request->is('post')) {
			$entity = $this->Users->newEntity($this->request->data(), ['validate' => 'NotUnique']);
			
			//Checks for reCAPTCHA, if requested
			if(config('security.recaptcha') && !$this->Recaptcha->check()) {
				$this->Flash->error($this->Recaptcha->getError());
			}
			if(!$entity->errors()) {
				$user = $this->Users->find('active')
					->select(['id', 'email', 'first_name', 'last_name'])
					->where(['email' => $this->request->data('email')])
					->first();

				if(!$user->isEmpty()) {
					//Gets the token
					$token = $this->Token->create($user->email, ['type' => 'forgot_password', 'user_id' => $user->id]);
					
					//Sends email
					$this->getMailer('MeCms.User')
						->set('url', Router::url(['_name' => 'reset_password', $user->id, $token], TRUE))
						->send('forgot_password', [$user]);

					$this->Flash->success(__d('me_cms', 'We have sent you an email to reset your password'));
					$this->redirect(['_name' => 'login']);
				}
				else
					$this->Flash->error(__d('me_cms', 'No account found'));
			}
			else
				$this->Flash->error(__d('me_cms', 'The form has not been filled in correctly'));
		}
		else
			$entity = $this->Users->newEntity(NULL, ['validate' => 'NotUnique']);
			
		$this->set('user', $entity);

		$this->viewBuilder()->layout('login');
	}

	/**
	 * Login
	 * @return boolean
	 * @uses _loginWithCookie()
	 */
	public function login() {
		//Tries to login with cookies, if the login with cookies is enabled
		if(config('users.cookies_login'))
			$this->_loginWithCookie();
		
		if($this->request->is('post')) {			
			if($user = $this->Auth->identify()) {
				//Checks if the user is banned or if is disabled (the account should still be enabled)
				if($user['banned'] || !$user['active']) {
					if($user['banned'])
						$this->Flash->error(__d('me_cms', 'Your account has been banned by an admin'));
					elseif(!$user['active'])
						$this->Flash->error(__d('me_cms', 'Your account has not been activated yet'));
					
					return $this->_logout();
				}
				
				//Saves the login data in a cookie, if it was requested
				if($this->request->data('remember_me'))
					$this->Cookie->config(['expires' => '+365 days'])
						->write('login', ['username' => $this->request->data('username'), 'password' => $this->request->data('password')]);
				
				$this->Auth->setUser($user);
				return $this->redirect($this->Auth->redirectUrl());
			}
			else
				$this->Flash->error(__d('me_cms', 'Invalid username or password'));
		}
		
		$this->viewBuilder()->layout('login');
	}

	/**
	 * Logout
	 * @return bool
	 * @uses _logout()
	 */
	public function logout() {
		$this->Flash->success(__d('me_cms', 'You are successfully logged out'));
		
		return $this->_logout();
	}
	
	/**
	 * Resends the activation mail
	 * @uses MeTools\Controller\Component\Recaptcha::check()
	 * @uses MeTools\Controller\Component\Recaptcha::getError()
	 * @uses _send_activation_mail()
	 */
	public function resend_activation() {
		//Checks if signup is enabled and if accounts will be enabled by the user via email
		if(!config('users.signup') && config('users.activation') === 1) {
			$this->Flash->error(__d('me_cms', 'Disabled'));
			$this->redirect(['_name' => 'login']);
		}
				
		if($this->request->is('post')) {
			$entity = $this->Users->newEntity($this->request->data(), ['validate' => 'NotUnique']);

			//Checks for reCAPTCHA, if requested
			if(config('security.recaptcha') && !$this->Recaptcha->check()) {
				$this->Flash->error($this->Recaptcha->getError());
			}
			elseif(!$entity->errors()) {
				$user = $this->Users->find('pending')
					->select(['id', 'email', 'first_name', 'last_name'])
					->where(['email' => $this->request->data('email')])
					->first();

				if(!$user->isEmpty()) {
					//Sends the activation mail
					$this->_send_activation_mail($user);

					$this->Flash->success(__d('me_cms', 'We send you an email to activate your account'));
					$this->redirect(['_name' => 'login']);
				}
				else
					$this->Flash->error(__d('me_cms', 'No account found'));
			}
			else
				$this->Flash->error(__d('me_cms', 'The form has not been filled in correctly'));
		}
		else
			$entity = $this->Users->newEntity(NULL, ['validate' => 'OnlyCheck']);
		
		$this->set('user', $entity);
		
		$this->viewBuilder()->layout('login');
	}
	
	/**
	 * Resets password
	 * @param string $id User ID
	 * @param string $token Token
	 * @uses MeTools\Controller\Component\Token::check()
	 * @uses MeTools\Controller\Component\Token::delete()
	 * @throws NotFoundException
	 */
	public function reset_password($id, $token) {
		//Checks for token
		if(!$this->Token->check($token, ['type' => 'forgot_password', 'user_id' => $id])) {
			$this->Flash->error(__d('me_cms', 'Invalid token'));
			$this->redirect(['_name' => 'login']);
		}
		
		$user = $this->Users->find('active')
			->select(['id'])
			->where(compact('id'))
			->first();
		
		if($user->isEmpty())
			throw new NotFoundException(__d('me_cms', 'No account found'));

		if($this->request->is(['patch', 'post', 'put'])) {
			$user = $this->Users->patchEntity($user, $this->request->data);
			
			if($this->Users->save($user)) {
				//Deletes the token
				$this->Token->delete($token);
				
				$this->Flash->success(__d('me_cms', 'The password has been edited'));
				$this->redirect(['_name' => 'login']);
			}
			else
				$this->Flash->error(__d('me_cms', 'The password has not been edited'));
		}
		
		$this->set(compact('user'));
		
		$this->viewBuilder()->layout('login');
	}
	
	/**
	 * Internal function to send the activation mail
	 * @param object $user Users entity
	 * @return boolean
	 * @uses MeCms\Mailer\UserMailer::activation_mail()
	 * @uses MeCms\Network\Email\Email
	 * @uses MeTools\Controller\Component\Token::create()
	 */
	protected function _send_activation_mail($user) {
		//Gets the token
		$token = $this->Token->create($user->email, ['type' => 'signup', 'user_id' => $user->id]);
		
		//Sends email
		return $this->getMailer('MeCms.User')
			->set('url', Router::url(['_name' => 'activate_account', $user->id, $token], TRUE))
			->send('activation_mail', [$user]);
	}
	
	/**
	 * Sign up
	 * @uses MeTools\Controller\Component\Recaptcha::check()
	 * @uses MeTools\Controller\Component\Recaptcha::getError()
	 * @uses _send_activation_mail()
	 */
	public function signup() {		
		//Checks if signup is enabled
		if(!config('users.signup')) {
			$this->Flash->error(__d('me_cms', 'Disabled'));
			$this->redirect(['_name' => 'login']);
		}
		
		$this->request->data += [
			'group_id'	=> config('users.default_group'),
			'active'	=> (bool) !config('users.activation')
		];
				
        $user = $this->Users->newEntity();
		
        if($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
			
			//Checks for reCAPTCHA, if requested
			if(config('security.recaptcha') && !$this->Recaptcha->check()) {
				$this->Flash->error($this->Recaptcha->getError());
			}
            elseif($this->Users->save($user)) {
				switch(config('users.activation')) {
					//The account will be enabled by an administrator
					case 2:
						$this->Flash->success(__d('me_cms', 'The account has been created, but it needs to be activated by an admin'));
						break;
					//The account will be enabled by the user via email (default)
					case 1:
						//Sends the activation mail
						$this->_send_activation_mail($user);
						
						$this->Flash->success(__d('me_cms', 'We send you an email to activate your account'));
						break;
					//No activation required, the account is immediately active
					default:
						$this->Flash->success(__d('me_cms', 'Account created. Now you can login'));
						break;
				}
				
				return $this->redirect(['action' => 'index']);
            } 
			else
				$this->Flash->error(__d('me_cms', 'The account has not been created'));
        }

        $this->set(compact('user'));
		
		$this->viewBuilder()->layout('login');
	}
}