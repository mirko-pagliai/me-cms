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

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Mailer\MailerAwareTrait;
use MeCms\Model\Entity\User;
use Symfony\Component\Finder\Finder;
use Thumber\Cake\Utility\ThumbManager;

/**
 * Users controller
 * @property \MeTools\Controller\Component\UploaderComponent $Uploader
 * @property \MeCms\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * Called before the controller action
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        $parent = parent::beforeFilter($event);
        if ($parent instanceof Response) {
            return $parent;
        }

        if ($this->getRequest()->is('action', ['index', 'add', 'edit'])) {
            $groups = $this->Users->UsersGroups->getList()->all();
            if ($groups->isEmpty()) {
                $this->Flash->alert(__d('me_cms', 'You must first create an user group'));

                return $this->redirect(['controller' => 'UsersGroups', 'action' => 'index']);
            }

            $this->set(compact('groups'));
        }
    }

    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(User $User): bool
    {
        /**
         * Every user:
         *  - can change his own password;
         *  - can change his own profile picture;
         *  - can view his last logins.
         */
        if ($this->getRequest()->is('action', ['changePassword', 'changePicture', 'lastLogin'])) {
            return true;
        }

        //Only admins can activate account and delete users
        if ($this->getRequest()->is('action', ['activate', 'delete'])) {
            return $User->get('group')->get('name') === 'admin';
        }

        return parent::isAuthorized($User);
    }

    /**
     * Lists users
     * @return void
     */
    public function index(): void
    {
        $query = $this->Users->find()->contain(['UsersGroups' => ['fields' => ['id', 'label']]]);

        $this->paginate['order'] = ['username' => 'ASC'];

        $users = $this->paginate($this->Users->queryFromFilter($query, $this->getRequest()->getQueryParams()));

        $this->set(compact('users'));
    }

    /**
     * Views user
     * @param string $id User ID
     * @return void
     */
    public function view(string $id): void
    {
        $user = $this->Users->findById($id)
            ->contain(['UsersGroups' => ['fields' => ['id', 'label']]])
            ->firstOrFail();

        $this->set(compact('user'));

        if (getConfig('users.login_log')) {
            $this->set('loginLog', $this->LoginRecorder->setConfig('user', $id)->read());
        }
    }

    /**
     * Adds user
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $user = $this->Users->patchEntity($user, $this->getRequest()->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('user'));
    }

    /**
     * Edits user
     * @param string $id User ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $user = $this->Users->get($id);

        //Only the admin founder can edit others admin users
        if ($user->get('group_id') === 1 && $this->Authentication->getId() !== 1) {
            $this->Flash->alert(I18N_ONLY_ADMIN_FOUNDER);

            return $this->redirectMatchingReferer(['action' => 'index']);
        }

        $user = $this->Users->patchEntity($user, $this->getRequest()->getData(), ['validate' => 'EmptyPassword']);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            if ($this->Users->save($user)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('user'));
    }

    /**
     * Deletes user
     * @param string $id User ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $User = $this->Users->get($id);

        //Cannot delete the admin founder
        if ($User->get('id') === 1) {
            $this->Flash->error(__d('me_cms', 'You cannot delete the admin founder'));
        //Only the admin founder can delete others admin users
        } elseif ($User->get('group_id') === 1 && $this->Authentication->getId() !== 1) {
            $this->Flash->alert(I18N_ONLY_ADMIN_FOUNDER);
        } elseif ($User->get('post_count')) {
            $this->Flash->alert(I18N_BEFORE_DELETE);
        } else {
            $this->Users->deleteOrFail($User);
            $this->Flash->success(I18N_OPERATION_OK);
        }

        return $this->redirectMatchingReferer(['action' => 'index']);
    }

    /**
     * Activates account
     * @param string $id User ID
     * @return \Cake\Http\Response|null
     */
    public function activate(string $id): ?Response
    {
        $this->Users->save($this->Users->get($id)->set('active', true));
        $this->Flash->success(I18N_OPERATION_OK);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }

    /**
     * Changes the user's password
     * @return \Cake\Http\Response|null|void
     * @see \MeCms\Mailer\UserMailer::changePassword()
     */
    public function changePassword()
    {
        $user = $this->Users->get($this->Authentication->getId());

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->getRequest()->getData());

            if ($this->Users->save($user)) {
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
     * @return void
     * @throws \Tools\Exception\ObjectWrongInstanceException|\ErrorException
     */
    public function changePicture()
    {
        /** @var ?\Laminas\Diactoros\UploadedFile $UploadedFile */
        $UploadedFile = $this->getRequest()->getData('file');

        if ($this->getRequest()->is(['patch', 'post', 'put']) && $UploadedFile) {
            $id = $this->Authentication->getId();

            //Deletes any picture that already exists
            foreach ((new Finder())->files()->name('/^' . $id . '\..+/')->in(USER_PICTURES) as $file) {
                @unlink($file->getPathname());
            }

            $uploaded = $this->Uploader->setFile($UploadedFile)
                ->mimetype('image')
                ->save(USER_PICTURES, $id . '.' . pathinfo($UploadedFile->getClientFilename() ?: '', PATHINFO_EXTENSION));
            if (!$uploaded) {
                $this->setUploadError($this->Uploader->getError());

                return;
            }

            //Clears similar thumbnails
            (new ThumbManager())->clear($uploaded);
        }
    }

    /**
     * Displays the login log
     * @return \Cake\Http\Response|null|void
     */
    public function lastLogin()
    {
        //Checks if login logs are enabled
        if (!getConfig('users.login_log')) {
            $this->Flash->error(I18N_DISABLED);

            return $this->redirect(['_name' => 'dashboard']);
        }

        $this->set('loginLog', $this->LoginRecorder->setConfig('user', $this->Authentication->getId())->read());
    }
}
