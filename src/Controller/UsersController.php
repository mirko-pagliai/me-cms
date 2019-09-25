<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MeCms\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Http\Cookie\Cookie;
use Cake\Log\Log;
use Cake\Mailer\MailerAwareTrait;
use Cake\Routing\Router;
use DateTime;
use MeCms\Controller\AppController;

/**
 * Users controller
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * Internal method to login with cookie
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::write()
     * @uses buildLogout()
     */
    protected function loginWithCookie()
    {
        list($username, $password) = array_values($this->getRequest()->getCookie('login', [null, null]));
        if (!$username || !$password) {
            return null;
        }

        //Tries to login
        $this->setRequest($this->getRequest()->withParsedBody(compact('username', 'password')));
        $user = $this->Auth->identify();
        if (!$user || !$user['active'] || $user['banned']) {
            return $this->buildLogout();
        }

        $this->Auth->setUser($user);
        $this->LoginRecorder->setConfig('user', $user['id'])->write();

        return $this->redirect($this->Auth->redirectUrl());
    }

    /**
     * Internal method to logout.
     * Deletes some cookies and KCFinder session.
     * @return \Cake\Network\Response|null
     */
    protected function buildLogout()
    {
        $request = $this->getRequest()->getSession()->delete('KCFINDER');
        $cookies = $request->getCookieCollection()->remove('login');
        $this->setRequest($request->withCookieCollection($cookies));

        return $this->redirect($this->Auth->logout());
    }

    /**
     * Internal method to send the activation mail
     * @param \MeCms\Model\Entity\User $user User entity
     * @return bool
     * @see \MeCms\Mailer\UserMailer::activation()
     */
    protected function sendActivationMail($user)
    {
        $token = $this->Token->create($user->get('email'), ['type' => 'signup', 'user_id' => $user->get('id')]);

        return (bool)$this->getMailer('MeCms.User')
            ->set('url', Router::url(['_name' => 'activation', $user->get('id'), $token], true))
            ->send('activation', [$user]);
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Tokens.Token');
        $this->loadComponent('MeCms.LoginRecorder');
    }

    /**
     * Called before the controller action.
     * You can use this method to perform logic that needs to happen before
     *  each controller action.
     * @param \Cake\Event\Event $event An Event instance
     * @return \Cake\Network\Response|null|void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        //Checks if the user is already logged in
        if (!$this->getRequest()->isAction('logout') && $this->Auth->isLogged()) {
            return $this->redirect(['_name' => 'dashboard']);
        }
    }

    /**
     * Activation (activates account)
     * @param string $id User ID
     * @param string $token Token
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function activation($id, $token)
    {
        $tokenExists = $this->Token->check($token, ['type' => 'signup', 'user_id' => $id]);
        is_true_or_fail($tokenExists, __d('me_cms', 'Invalid token'), RecordNotFoundException::class);
        $this->Token->delete($token);

        $update = $this->Users->findPendingById($id)
            ->update()
            ->set(['active' => true])
            ->execute();

        list($method, $message) = ['error', I18N_OPERATION_NOT_OK];
        if ($update->count()) {
            list($method, $message) = ['success', I18N_OPERATION_OK];
        }
        call_user_func([$this->Flash, $method], $message);

        return $this->redirect(['_name' => 'login']);
    }

    /**
     * Activation resend (resends the activation mail)
     * @return \Cake\Network\Response|null|void
     * @uses sendActivationMail()
     */
    public function activationResend()
    {
        //Checks if signup is enabled and if accounts will be enabled by the
        //  user via email
        if (!getConfig('users.signup') && getConfig('users.activation') === 1) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        $entity = $this->Users->newEntity($this->getRequest()->getData(), ['validate' => 'DoNotRequirePresence']);

        if ($this->getRequest()->is('post')) {
            //Checks for reCAPTCHA, if requested
            $message = __d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA');
            if (!getConfig('security.recaptcha') || $this->Recaptcha->verify()) {
                if (!$entity->getErrors()) {
                    $user = $this->Users->findPendingByEmail($this->getRequest()->getData('email'))->first();
                    $message = __d('me_cms', 'No valid account was found');

                    if ($user && $this->sendActivationMail($user)) {
                        $this->Flash->success(__d('me_cms', 'We send you an email to activate your account'));

                        return $this->redirect(['_name' => 'login']);
                    }

                    if ($this->getRequest()->getData('email')) {
                        Log::error(sprintf(
                            '%s - Resend activation request with invalid email `%s`',
                            $this->getRequest()->clientIp(),
                            $this->getRequest()->getData('email')
                        ), 'users');
                    }
                }
            }
            $this->Flash->error($message);
        }

        $this->set('user', $entity);
        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Login
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::write()
     * @uses loginWithCookie()
     */
    public function login()
    {
        //Tries to login with cookies, if the login with cookies is enabled
        if (getConfig('users.cookies_login')) {
            $this->loginWithCookie();
        }

        if ($this->getRequest()->is('post')) {
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

                    return $this->buildLogout();
                }

                $this->Auth->setUser($user);

                $this->LoginRecorder->setConfig('user', $user['id'])->write();

                //Saves the login data as cookies, if requested
                if ($this->getRequest()->getData('remember_me')) {
                    $cookie = new Cookie('login', [
                        'username' => $this->getRequest()->getData('username'),
                        'password' => $this->getRequest()->getData('password'),
                    ], new DateTime('+1 year'));
                    $this->setResponse($this->getResponse()->withCookie($cookie));
                }

                return $this->redirect($this->Auth->redirectUrl());
            }

            if ($this->getRequest()->getData('username') && $this->getRequest()->getData('password')) {
                Log::error(sprintf(
                    '%s - Failed login with username `%s` and password `%s`',
                    $this->getRequest()->clientIp(),
                    $this->getRequest()->getData('username'),
                    $this->getRequest()->getData('password')
                ), 'users');
            }

            $this->Flash->error(__d('me_cms', 'Invalid username or password'));
        }

        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Logout
     * @return void
     * @uses buildLogout()
     */
    public function logout()
    {
        $this->Flash->success(__d('me_cms', 'You are successfully logged out'));

        $this->buildLogout();
    }

    /**
     * Password forgot (requests a new password)
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Mailer\UserMailer::passwordForgot()
     */
    public function passwordForgot()
    {
        //Checks if reset password is enabled
        if (!getConfig('users.reset_password')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        $entity = $this->Users->newEntity($this->getRequest()->getData(), ['validate' => 'DoNotRequirePresence']);

        if ($this->getRequest()->is('post')) {
            //Checks for reCAPTCHA, if requested
            $message = __d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA');
            if (!getConfig('security.recaptcha') || $this->Recaptcha->verify()) {
                $message = __d('me_cms', 'No account found');
                $user = $this->Users->findActiveByEmail($this->getRequest()->getData('email'))->first();

                if ($user) {
                    $token = $this->Token->create(
                        $user->get('email'),
                        ['type' => 'password_forgot', 'user_id' => $user->get('id')]
                    );
                    $this->getMailer('MeCms.User')
                        ->set('url', Router::url(['_name' => 'passwordReset', $user->id, $token], true))
                        ->send('passwordForgot', [$user]);
                    $this->Flash->success(__d('me_cms', 'We have sent you an email to reset your password'));

                    return $this->redirect(['_name' => 'login']);
                }

                if ($this->getRequest()->getData('email')) {
                    Log::error(sprintf(
                        '%s - Forgot password request with invalid email `%s`',
                        $this->getRequest()->clientIp(),
                        $this->getRequest()->getData('email')
                    ), 'users');
                }
            }
            $this->Flash->error($message);
        }

        $this->set('user', $entity);
        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Password reset
     * @param string $id User ID
     * @param string $token Token
     * @return \Cake\Network\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function passwordReset($id, $token)
    {
        $tokenExists = $this->Token->check($token, ['type' => 'password_forgot', 'user_id' => $id]);
        is_true_or_fail($tokenExists, __d('me_cms', 'Invalid token'), RecordNotFoundException::class);

        $user = $this->Users->findActiveById($id)->firstOrFail();

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->getRequest()->getData());

            if ($user->isDirty() && $this->Users->save($user)) {
                $this->Token->delete($token);
                $this->Flash->success(__d('me_cms', 'The password has been edited'));

                return $this->redirect(['_name' => 'login']);
            }

            $this->Flash->error(__d('me_cms', 'The password has not been edited'));
        }

        $this->set(compact('user'));
        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Sign up
     * @return \Cake\Network\Response|null|void
     * @uses sendActivationMail()
     */
    public function signup()
    {
        //Checks if signup is enabled
        if (!getConfig('users.signup')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        $user = $this->Users->newEntity();

        if ($this->getRequest()->is('post')) {
            $user = $this->Users->patchEntity($user, $this->getRequest()->getData());

            $user->set('group_id', getConfigOrFail('users.default_group'))
                ->set('active', (bool)!getConfig('users.activation'));

            //Checks for reCAPTCHA, if requested
            $message = __d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA');
            if (!getConfig('security.recaptcha') || $this->Recaptcha->verify()) {
                $message = __d('me_cms', 'The account has not been created');

                if ($this->Users->save($user)) {
                    switch (getConfig('users.activation')) {
                        //The account will be enabled by an administrator
                        case 2:
                            $message = __d('me_cms', 'Account created, but it needs to be activated by an admin');
                            break;
                        //The account will be enabled by the user via email
                        //  (default)
                        case 1:
                            //Sends the activation mail
                            $this->sendActivationMail($user);
                            $message = __d('me_cms', 'We send you an email to activate your account');
                            break;
                        //No activation required, the account is immediately active
                        default:
                            $message = __d('me_cms', 'Account created. Now you can login');
                            break;
                    }
                    $this->Flash->success($message);

                    return $this->redirect(['_name' => 'homepage']);
                }
            }
            $this->Flash->error($message);
        }

        $this->set(compact('user'));
        $this->viewBuilder()->setLayout('login');
    }
}
