<?php
declare(strict_types=1);

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

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Mailer\MailerAwareTrait;
use Cake\Routing\Router;

/**
 * Users controller
 * @property \MeCms\Controller\Component\LoginRecorderComponent $LoginRecorder
 * @property \Tokens\Controller\Component\TokenComponent $Token
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * Internal method to send the activation mail
     * @param \Cake\Datasource\EntityInterface $user User entity
     * @return bool
     * @see \MeCms\Mailer\UserMailer::activation()
     */
    protected function sendActivationMail(EntityInterface $user): bool
    {
        $token = $this->Token->create($user->get('email'), ['type' => 'signup', 'user_id' => $user->get('id')]);

        return (bool)$this->getMailer('MeCms.User')
            ->setViewVars('url', Router::url(['_name' => 'activation', (string)$user->get('id'), $token], true))
            ->send('activation', [$user]);
    }

    /**
     * Initialization hook method
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Tokens.Token');
        $this->loadComponent('MeCms.LoginRecorder');
    }

    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|void
     */
    public function beforeFilter(EventInterface $event)
    {
        //Checks if the user is already logged in
        if (!$this->getRequest()->is('action', 'logout') && $this->getRequest()->getAttribute('identity')) {
            return $this->redirect(['_name' => 'dashboard']);
        }

        return parent::beforeFilter($event);
    }

    /**
     * Activation (activates account)
     * @param string $id User ID
     * @param string $token Token
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function activation(string $id, string $token): ?Response
    {
        if (!$this->Token->check($token, ['type' => 'signup', 'user_id' => $id])) {
            throw new RecordNotFoundException(__d('me_cms', 'Invalid token'));
        }
        $this->Token->delete($token);

        $update = $this->Users->findPendingById($id)
            ->update()
            ->set(['active' => true])
            ->execute();

        [$method, $message] = $update->count() ? ['success', I18N_OPERATION_OK] : ['error', I18N_OPERATION_NOT_OK];
        $this->Flash->$method($message);

        return $this->redirect(['_name' => 'login']);
    }

    /**
     * Activation resend (resends the activation mail)
     * @return \Cake\Http\Response|null|void
     * @uses sendActivationMail()
     */
    public function activationResend()
    {
        //Checks if signup is enabled and if accounts will be enabled by the user via email
        if (!getConfig('users.signup') && getConfig('users.activation') === 1) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        $entity = $this->Users->newEntity($this->getRequest()->getData(), ['validate' => 'DoNotRequirePresence']);

        if ($this->getRequest()->is('post')) {
            //Checks for reCAPTCHA, if requested
            $message = __d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA');
            if (!getConfig('security.recaptcha') || (isset($this->Recaptcha) && $this->Recaptcha->verify())) {
                if (!$entity->getErrors()) {
                    $email = $this->getRequest()->getData('email');
                    $user = $this->Users->findPendingByEmail($email)->first();
                    $message = __d('me_cms', 'No valid account was found');

                    if ($user && $this->sendActivationMail($user)) {
                        $this->Flash->success(__d('me_cms', 'We send you an email to activate your account'));

                        return $this->redirect(['_name' => 'login']);
                    }

                    if ($email) {
                        Log::error(sprintf(
                            '%s - Resend activation request: invalid email `%s`',
                            $this->getRequest()->clientIp(),
                            $email
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
     * @return \Cake\Http\Response|null|void
     * @see \MeCms\Plugin::getAuthenticationService()
     * @todo Should return some error message for accounts not yet activated or banned
     */
    public function login()
    {
        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            /** @var \Authentication\Identity $Identity */
            $Identity = $this->Authentication->getIdentity();
            $this->LoginRecorder->setConfig('user', $Identity->get('id'))->write();

            return $this->redirect($this->Authentication->getLoginRedirect() ?? Router::url(['_name' => 'dashboard'], true));
        }

        if ($this->getRequest()->is('post')) {
            $this->Flash->error(__d('me_cms', 'Invalid username or password'));

            $this->log(sprintf(
                '%s - Failed login: username `%s`, password `%s`',
                $this->getRequest()->clientIp(),
                $this->getRequest()->getData('username'),
                $this->getRequest()->getData('password')
            ), 'error', 'users');
        }

        $this->viewBuilder()->setLayout('login');
    }

    /**
     * Logout
     * @return \Cake\Http\Response|null
     */
    public function logout(): ?Response
    {
        $this->Flash->success(__d('me_cms', 'You are successfully logged out'));

        return $this->redirect($this->Authentication->logout());
    }

    /**
     * Password forgot (requests a new password)
     * @return \Cake\Http\Response|null|void
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
            if (!getConfig('security.recaptcha') || (isset($this->Recaptcha) && $this->Recaptcha->verify())) {
                $message = __d('me_cms', 'No account found');
                $email = $this->getRequest()->getData('email');
                $user = $this->Users->findActiveByEmail($email)->first();

                if ($user) {
                    $token = $this->Token->create($email, ['type' => 'password_forgot', 'user_id' => $user->get('id')]);
                    $this->getMailer('MeCms.User')
                        ->setViewVars('url', Router::url(['_name' => 'passwordReset', (string)$user->get('id'), $token], true))
                        ->send('passwordForgot', [$user]);
                    $this->Flash->success(__d('me_cms', 'We have sent you an email to reset your password'));

                    return $this->redirect(['_name' => 'login']);
                }

                if ($this->getRequest()->getData('email')) {
                    Log::error(sprintf(
                        '%s - Forgot password request: invalid email `%s`',
                        $this->getRequest()->clientIp(),
                        $email
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
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function passwordReset(string $id, string $token)
    {
        if (!$this->Token->check($token, ['type' => 'password_forgot', 'user_id' => $id])) {
            throw new RecordNotFoundException(__d('me_cms', 'Invalid token'));
        }

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
     * @return \Cake\Http\Response|null|void
     */
    public function signup()
    {
        //Checks if signup is enabled
        if (!getConfig('users.signup')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'homepage']);
        }

        $user = $this->Users->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $user = $this->Users->patchEntity($user, $this->getRequest()->getData());

            $user->set('group_id', getConfigOrFail('users.default_group'))
                ->set('active', !getConfig('users.activation'));

            //Checks for reCAPTCHA, if requested
            $message = __d('me_cms', 'You must fill in the {0} control correctly', 'reCAPTCHA');
            if (!getConfig('security.recaptcha') || (isset($this->Recaptcha) && $this->Recaptcha->verify())) {
                $message = __d('me_cms', 'The account has not been created');

                if ($this->Users->save($user)) {
                    switch (getConfig('users.activation')) {
                        //The account will be enabled by an administrator
                        case 2:
                            $message = __d('me_cms', 'Account created, but it needs to be activated by an admin');
                            break;
                        //The account will be enabled by the user via email (default)
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
