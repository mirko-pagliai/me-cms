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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use Cake\Routing\Router;
use Cake\Utility\Security;
use MeCms\Controller\AppController;

/**
 * Users controller
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController {
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
	}
	
	/**
	 * Internal function to login with cookie
	 * @return mixed
	 * @uses Cake\Utility\Security::decrypt()
	 * @uses _logout()
	 */
	private function _loginWithCookie() {
		//Checks if the cookie exists
		if(!$this->Cookie->check('login'))
			return FALSE;
		
		//Decrypts and unserializes the login datas
		$this->request->data = json_decode(Security::decrypt($this->Cookie->read('login'), config('security.crypt_key')), TRUE);
		
		//Tries to login...
		if(($user = $this->Auth->identify()) && $user['active'] && !$user['banned']) {
			$this->Auth->setUser($user);
			return $this->redirect($this->Auth->redirectUrl());
		}
		
		return $this->_logout();
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
	 * @throws \Cake\Network\Exception\NotFoundException
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
		
		$user->active = TRUE;
		
		if(empty($user))
			throw new \Cake\Network\Exception\NotFoundException(__d('me_cms', 'No account found'));
				
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
	 * @uses MeCms\Network\Email\Email
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
			elseif(!$entity->errors()) {
				$user = $this->Users->find('active')
					->select(['id', 'email', 'first_name', 'last_name'])
					->where(['email' => $this->request->data('email')])
					->first();

				if(!empty($user)) {
					//Gets the token
					$token = $this->Token->create($user->email, ['type' => 'forgot_password', 'user_id' => $user->id]);

					//Sends email
					(new \MeCms\Network\Email\Email)->to([$user->email => $user->full_name])
						->subject(__d('me_cms', 'Reset your password'))
						->template('MeCms.Users/forgot_password')
						->set([
							'full_name' => $user->full_name,
							'url'		=> Router::url(['_name' => 'reset_password', $user->id, $token], TRUE)
						])
						->send();

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

		$this->layout = 'login';
	}

	/**
	 * Login
	 * @return boolean
	 * @uses Cake\Utility\Security::encrypt()
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
				if($this->request->data('remember_me')) {						
					$this->Cookie->config(['expires' => '+365 days']);
					$this->Cookie->write('login', Security::encrypt(json_encode([
						'username' => $this->request->data('username'), 
						'password' => $this->request->data('password')
					]), config('security.crypt_key')));
				}
				
				$this->Auth->setUser($user);
				return $this->redirect($this->Auth->redirectUrl());
			}
			else
				$this->Flash->error(__d('me_cms', 'Invalid username or password'));
		}
		
		$this->layout = 'login';
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

				if(!empty($user)) {
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
		
		$this->layout = 'login';
	}
	
	/**
	 * Resets password
	 * @param string $id User ID
	 * @param string $token Token
	 * @uses MeTools\Controller\Component\Token::check()
	 * @uses MeTools\Controller\Component\Token::delete()
	 * @throws \Cake\Network\Exception\NotFoundException
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
		
		if(empty($user))
			throw new \Cake\Network\Exception\NotFoundException(__d('me_cms', 'No account found'));

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
		
		$this->layout = 'login';
	}
	
	/**
	 * Internal function to send the activation mail
	 * @param object $user Users entity
	 * @return boolean
	 * @uses MeCms\Network\Email\Email
	 * @uses MeTools\Controller\Component\Token::create()
	 */
	protected function _send_activation_mail($user) {
		//Gets the token
		$token = $this->Token->create($user->email, ['type' => 'signup', 'user_id' => $user->id]);
		
		//Sends email
		return (new \MeCms\Network\Email\Email)->to([$user->email => $user->full_name])
			->subject(__d('me_cms', 'Activate your account'))
			->template('MeCms.Users/activate_account')
			->set([
				'full_name' => $user->full_name,
				'url'		=> Router::url(['_name' => 'activate_account', $user->id, $token], TRUE)
			])
			->send();
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
		
		$this->layout = 'login';
	}
}