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
namespace MeCms\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;
use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\InternalErrorException;
use Cake\Routing\Router;
use MeCms\Controller\AppController;
use MeCms\Utility\LoginRecorder;

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
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);

        //Checks if the user is already logged in
        if (!$this->request->isAction('logout') && $this->Auth->isLogged()) {
            return $this->redirect(['_name' => 'dashboard']);
        }
    }

    /**
     * Internal function to login with cookie
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Utility\LoginRecorder::write()
     * @uses _logout()
     */
    protected function _loginWithCookie()
    {
        //Checks if the cookie exists
        if (!$this->Cookie->read('login')) {
            return;
        }

        $this->request->data = $this->Cookie->read('login');

        //Tries to login
        if (!empty($this->request->data['username']) && !empty($this->request->data['password'])) {
            $user = $this->Auth->identify();

            if ($user && $user['active'] && !$user['banned']) {
                //Saves the login log
                (new LoginRecorder($user['id']))->write();

                $this->Auth->setUser($user);

                return $this->redirect($this->Auth->redirectUrl());
            }
        }

        //Internal function to logout
        $this->_logout();
    }

    /**
     * Internal function to logout
     * @return \Cake\Network\Response|null
     */
    protected function _logout()
    {
        //Deletes the login cookie
        $this->Cookie->delete('login');

        //Deletes the KCFinder session
        $this->request->session()->delete('KCFINDER');

        //Deletes JS cookie
        setcookie('sidebar-lastmenu', '', 1, '/');

        return $this->redirect($this->Auth->logout());
    }

    /**
     * Internal function to send the activation mail
     * @param object $user Users entity
     * @return bool
     * @throws InternalErrorException
     * @uses MeCms\Mailer\UserMailer::activateAccount()
     * @uses MeCms\Network\Email\Email
     */
    protected function _sendActivationMail($user)
    {
        //Creates the token
        $token = $this->Token->create($user->email, ['type' => 'signup', 'user_id' => $user->id]);

        if (empty($token)) {
            throw new InternalErrorException(__d('me_cms', 'Failure when creating the token'));
        }

        //Sends email
        return $this->getMailer('MeCms.User')
            ->set('url', Router::url(['_name' => 'activateAccount', $user->id, $token], true))
            ->send('activateAccount', [$user]);
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Cookie');
        $this->loadComponent('Tokens.Token');
    }

    /**
     * Activates account
     * @param string $id User ID
     * @param string $token Token
     * @return \Cake\Network\Response|null
     * @throws RecordNotFoundException
     */
    public function activateAccount($id, $token)
    {
        //Checks for token
        if (!$this->Token->check($token, ['type' => 'signup', 'user_id' => $id])) {
            throw new RecordNotFoundException(__d('me_cms', 'Invalid token'));
        }

        $user = $this->Users->find('pending')
            ->select(['id'])
            ->where(compact('id'))
            ->firstOrFail();

        $user->active = true;

        if ($this->Users->save($user)) {
            $this->Flash->success(__d('me_cms', 'The account has been activated'));
        } else {
            $this->Flash->error(__d('me_cms', 'The account has not been activated'));
        }

        //Deletes the token
        $this->Token->delete($token);

        return $this->redirect(['_name' => 'login']);
    }

    /**
     * Requests a new password
     * @return \Cake\Network\Response|null|void
     * @throws InternalErrorException
     * @uses MeCms\Mailer\UserMailer::forgotPassword()
     * @uses MeTools\Controller\Component\Recaptcha::check()
     * @uses MeTools\Controller\Component\Recaptcha::getError()
     */
    public function forgotPassword()
    {
        //Checks if reset password is enabled
        if (!config('users.reset_password')) {
            $this->Flash->error(__d('me_cms', 'Disabled'));

            return $this->redirect(['_name' => 'homepage']);
        }

        $entity = $this->Users->newEntity($this->request->data(), ['validate' => 'DoNotRequirePresence']);

        if ($this->request->is('post')) {
            //Checks for reCAPTCHA, if requested
            if (config('security.recaptcha') && !$this->Recaptcha->check()) {
                $this->Flash->error($this->Recaptcha->getError());
            } elseif (!$entity->errors()) {
                $user = $this->Users->find('active')
                    ->select(['id', 'email', 'first_name', 'last_name'])
                    ->where(['email' => $this->request->data('email')])
                    ->first();

                if ($user) {
                    //Creates the token
                    $token = $this->Token->create($user->email, ['type' => 'forgot_password', 'user_id' => $user->id]);

                    if (empty($token)) {
                        throw new InternalErrorException(__d('me_cms', 'Failure when creating the token'));
                    }

                    //Sends email
                    $this->getMailer('MeCms.User')
                        ->set('url', Router::url(['_name' => 'resetPassword', $user->id, $token], true))
                        ->send('forgotPassword', [$user]);

                    $this->Flash->success(__d('me_cms', 'We have sent you an email to reset your password'));

                    return $this->redirect(['_name' => 'login']);
                } else {
                    //Logging
                    Log::error(sprintf(
                        '%s - Forgot password with incorrect email `%s`',
                        $this->request->clientIp(),
                        $this->request->data('email')
                    ), 'users');

                    $this->Flash->error(__d('me_cms', 'No account found'));
                }
            } else {
                $this->Flash->error(__d('me_cms', 'The form has not been filled in correctly'));
            }
        }

        $this->set('user', $entity);
        $this->viewBuilder()->layout('login');
    }

    /**
     * Login
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Utility\LoginRecorder::write()
     * @uses _loginWithCookie()
     */
    public function login()
    {
        //Tries to login with cookies, if the login with cookies is enabled
        if (config('users.cookies_login')) {
            $this->_loginWithCookie();
        }

        if ($this->request->is('post')) {
            $user = $this->Auth->identify();

            if ($user) {
                //Checks if the user is banned or if is disabled (the account
                //  should still be enabled)
                if ($user['banned'] || !$user['active']) {
                    if ($user['banned']) {
                        $this->Flash->error(__d('me_cms', 'Your account has been banned by an admin'));
                    } elseif (!$user['active']) {
                        $this->Flash->error(__d('me_cms', 'Your account has not been activated yet'));
                    }

                    return $this->_logout();
                }

                //Saves the login log
                (new LoginRecorder($user['id']))->write();

                //Saves the login data in a cookie, if it was requested
                if ($this->request->data('remember_me')) {
                    $this->Cookie->config(['expires' => '+365 days'])
                        ->write('login', [
                            'username' => $this->request->data('username'),
                            'password' => $this->request->data('password'),
                        ]);
                }

                $this->Auth->setUser($user);

                return $this->redirect($this->Auth->redirectUrl());
            } else {
                //Logging
                Log::error(sprintf(
                    '%s - Failed login with username `%s` and password `%s`',
                    $this->request->clientIp(),
                    $this->request->data('username'),
                    $this->request->data('password')
                ), 'users');

                $this->Flash->error(__d('me_cms', 'Invalid username or password'));
            }
        }

        $this->viewBuilder()->layout('login');
    }

    /**
     * Logout
     * @return void
     * @uses _logout()
     */
    public function logout()
    {
        $this->Flash->success(__d('me_cms', 'You are successfully logged out'));

        $this->_logout();
    }

    /**
     * Resends the activation mail
     * @return \Cake\Network\Response|null|void
     * @uses MeTools\Controller\Component\Recaptcha::check()
     * @uses MeTools\Controller\Component\Recaptcha::getError()
     * @uses _sendActivationMail()
     */
    public function resendActivation()
    {
        //Checks if signup is enabled and if accounts will be enabled by the
        //  user via email
        if (!config('users.signup') && config('users.activation') === 1) {
            $this->Flash->error(__d('me_cms', 'Disabled'));

            return $this->redirect(['_name' => 'login']);
        }

        $entity = $this->Users->newEntity($this->request->data(), ['validate' => 'DoNotRequirePresence']);

        if ($this->request->is('post')) {
            //Checks for reCAPTCHA, if requested
            if (config('security.recaptcha') && !$this->Recaptcha->check()) {
                $this->Flash->error($this->Recaptcha->getError());
            } elseif (!$entity->errors()) {
                $user = $this->Users->find('pending')
                    ->select(['id', 'email', 'first_name', 'last_name'])
                    ->where(['email' => $this->request->data('email')])
                    ->first();

                if ($user) {
                    //Sends the activation mail
                    $this->_sendActivationMail($user);

                    $this->Flash->success(__d('me_cms', 'We send you an email to activate your account'));

                    return $this->redirect(['_name' => 'login']);
                } else {
                    //Logging
                    Log::error(sprintf(
                        '%s - Resend activation with incorrect email `%s`',
                        $this->request->clientIp(),
                        $this->request->data('email')
                    ), 'users');

                    $this->Flash->error(__d('me_cms', 'No account found'));
                }
            } else {
                $this->Flash->error(__d('me_cms', 'The form has not been filled in correctly'));
            }
        }

        $this->set('user', $entity);
        $this->viewBuilder()->layout('login');
    }

    /**
     * Resets password
     * @param string $id User ID
     * @param string $token Token
     * @return \Cake\Network\Response|null|void
     * @throws RecordNotFoundException
     */
    public function resetPassword($id, $token)
    {
        //Checks for token
        if (!$this->Token->check($token, ['type' => 'forgot_password', 'user_id' => $id])) {
            throw new RecordNotFoundException(__d('me_cms', 'Invalid token'));
        }

        $user = $this->Users->find('active')
            ->select(['id'])
            ->where(compact('id'))
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__d('me_cms', 'The password has been edited'));

                return $this->redirect(['_name' => 'login']);
            } else {
                $this->Flash->error(__d('me_cms', 'The password has not been edited'));
            }

            //Deletes the token
            $this->Token->delete($token);
        }

        $this->set(compact('user'));

        $this->viewBuilder()->layout('login');
    }

    /**
     * Sign up
     * @return \Cake\Network\Response|null|void
     * @uses MeTools\Controller\Component\Recaptcha::check()
     * @uses MeTools\Controller\Component\Recaptcha::getError()
     * @uses _sendActivationMail()
     */
    public function signup()
    {
        //Checks if signup is enabled
        if (!config('users.signup')) {
            $this->Flash->error(__d('me_cms', 'Disabled'));

            return $this->redirect(['_name' => 'login']);
        }

        $this->request->data += [
            'group_id' => config('users.default_group'),
            'active' => (bool)!config('users.activation'),
        ];

        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);

            //Checks for reCAPTCHA, if requested
            if (config('security.recaptcha') && !$this->Recaptcha->check()) {
                $this->Flash->error($this->Recaptcha->getError());
            } elseif ($this->Users->save($user)) {
                switch (config('users.activation')) {
                    //The account will be enabled by an administrator
                    case 2:
                        $this->Flash->success(__d('me_cms', 'The account has been created, but it needs to be activated by an admin'));
                        break;
                    //The account will be enabled by the user via email
                    //  (default)
                    case 1:
                        //Sends the activation mail
                        $this->_sendActivationMail($user);

                        $this->Flash->success(__d('me_cms', 'We send you an email to activate your account'));
                        break;
                    //No activation required, the account is immediately active
                    default:
                        $this->Flash->success(__d('me_cms', 'Account created. Now you can login'));
                        break;
                }

                return $this->redirect(['_name' => 'homepage']);
            } else {
                $this->Flash->error(__d('me_cms', 'The account has not been created'));
            }
        }

        $this->set(compact('user'));

        $this->viewBuilder()->layout('login');
    }
}
