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
namespace MeCms\Controller\Admin;

use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Mailer\MailerAwareTrait;
use MeCms\Controller\AppController;
use Thumber\Utility\ThumbManager;

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
     * @uses MeCms\Model\Table\UsersGroupsTable::getList()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->isAction(['index', 'add', 'edit'])) {
            $groups = $this->Users->Groups->getList();

            if ($groups->isEmpty()) {
                $this->Flash->alert(__d('me_cms', 'You must first create an user group'));

                return $this->redirect(['controller' => 'UsersGroups', 'action' => 'index']);
            }

            $this->set(compact('groups'));
        }
    }

    /**
     * Initialization hook method
     * @return void
     * @uses MeCms\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads components
        $this->loadComponent('MeCms.LoginRecorder');
    }

    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses \MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Every user can change his password
        if ($this->request->isAction('changePassword')) {
            return true;
        }

        //Only admins can activate account and delete users. Admins and managers can access other actions
        $group = $this->request->isAction(['activate', 'delete']) ? ['admin'] : ['admin', 'manager'];

        return $this->Auth->isGroup($group);
    }

    /**
     * Lists users
     * @return void
     * @uses \MeCms\Model\Table\UsersTable::queryFromFilter()
     */
    public function index()
    {
        $query = $this->Users->find()->contain(['Groups' => ['fields' => ['id', 'label']]]);

        $this->paginate['order'] = ['username' => 'ASC'];

        $users = $this->paginate($this->Users->queryFromFilter($query, $this->request->getQueryParams()));

        $this->set(compact('users'));
    }

    /**
     * Views user
     * @param string $id User ID
     * @return void
     * @uses \MeCms\Controller\Component\LoginRecorderComponent::read()
     */
    public function view($id)
    {
        $user = $this->Users->findById($id)
            ->contain(['Groups' => ['fields' => ['label']]])
            ->firstOrFail();

        $this->set(compact('user'));

        if (getConfig('users.login_log')) {
            $this->set('loginLog', $this->LoginRecorder->setConfig('user', $id)->read());
        }
    }

    /**
     * Adds user
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('user'));
    }

    /**
     * Edits user
     * @param string $id User ID
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Controller\Component\AuthComponent::isFounder()
     */
    public function edit($id)
    {
        $user = $this->Users->get($id);

        //Only the admin founder can edit others admin users
        if ($user->group_id === 1 && !$this->Auth->isFounder()) {
            $this->Flash->alert(I18N_ONLY_ADMIN_FOUNDER);

            return $this->redirect(['action' => 'index']);
        }

        $user = $this->Users->patchEntity($user, $this->request->getData(), ['validate' => 'EmptyPassword']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->Users->save($user)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('user'));
    }

    /**
     * Deletes user
     * @param string $id User ID
     * @return \Cake\Network\Response|null|void
     * @uses \MeCms\Controller\Component\AuthComponent::isFounder()
     */
    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);

        $user = $this->Users->get($id);

        //You cannot delete the admin founder
        if ($user->id === 1) {
            $this->Flash->error(__d('me_cms', 'You cannot delete the admin founder'));
        //Only the admin founder can delete others admin users
        } elseif ($user->group_id === 1 && !$this->Auth->isFounder()) {
            $this->Flash->alert(I18N_ONLY_ADMIN_FOUNDER);
        } elseif ($user->post_count) {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        } else {
            $this->Users->deleteOrFail($user);

            $this->Flash->success(I18N_OPERATION_OK);
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Activates account
     * @param string $id User ID
     * @return \Cake\Network\Response|null
     */
    public function activate($id)
    {
        $user = $this->Users->get($id);
        $this->Users->save($user->set('active', true));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Changes the user's password
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Mailer\UserMailer::changePassword()
     */
    public function changePassword()
    {
        $user = $this->Users->get($this->Auth->user('id'));

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                //Sends email
                $this->getMailer('MeCms.User')->send('changePassword', [$user]);
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['_name' => 'dashboard']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('user'));
    }

    /**
     * Changes the user's picture
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\AppController::setUploadError()
     * @uses MeTools\Controller\Component\UploaderComponent
     */
    public function changePicture()
    {
        if ($this->request->getData('file')) {
            $id = $this->Auth->user('id');

            //Deletes any picture that already exists
            foreach (((new Folder(USER_PICTURES))->find($id . '\..+')) as $filename) {
                @unlink(USER_PICTURES . $filename);
            }

            $filename = $id . '.' . pathinfo($this->request->getData('file')['tmp_name'], PATHINFO_EXTENSION);

            $uploaded = $this->Uploader->set($this->request->getData('file'))
                ->mimetype('image')
                ->save(USER_PICTURES, $filename);

            if (!$uploaded) {
                return $this->setUploadError($this->Uploader->getError());
            }

            //Updates the authentication data and clears similar thumbnails
            $this->Auth->setUser(array_merge($this->Auth->user(), ['picture' => $uploaded]));
            (new ThumbManager())->clear($uploaded);
        }
    }

    /**
     * Displays the login log
     * @return \Cake\Network\Response|null|void
     * @uses MeCms\Controller\Component\LoginRecorderComponent::read()
     */
    public function lastLogin()
    {
        //Checks if login logs are enabled
        if (!getConfig('users.login_log')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'dashboard']);
        }

        $this->set('loginLog', $this->LoginRecorder->setConfig('user', $this->Auth->user('id'))->read());
    }
}
