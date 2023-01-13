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

use Cake\Http\Response;
use MeCms\Model\Entity\User;

/**
 * UsersGroups controller
 * @property \MeCms\Model\Table\UsersGroupsTable $UsersGroups
 */
class UsersGroupsController extends AppController
{
    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(User $User): bool
    {
        //Only admins can access this controller
        return $User->get('group')->get('name') === 'admin';
    }

    /**
     * Lists usersGroups
     * @return void
     */
    public function index(): void
    {
        $this->paginate['order'] = ['name' => 'ASC'];

        $groups = $this->paginate($this->UsersGroups->find());

        $this->set(compact('groups'));
    }

    /**
     * Adds users group
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $group = $this->UsersGroups->newEmptyEntity();

        if ($this->getRequest()->is('post')) {
            $group = $this->UsersGroups->patchEntity($group, $this->getRequest()->getData());

            if ($this->UsersGroups->save($group)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('group'));
    }

    /**
     * Edits users group
     * @param string $id Users Group ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit(string $id)
    {
        $group = $this->UsersGroups->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $group = $this->UsersGroups->patchEntity($group, $this->getRequest()->getData());

            if ($this->UsersGroups->save($group)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirectMatchingReferer(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('group'));
    }

    /**
     * Deletes users group
     * @param string $id Users Group ID
     * @return \Cake\Http\Response|null
     */
    public function delete(string $id): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $group = $this->UsersGroups->get($id);

        //Before deleting, checks if the group is a necessary group or if the group has some users
        [$method, $message] = ['alert', I18N_BEFORE_DELETE];
        if ($id > 3 && !$group->get('user_count')) {
            $this->UsersGroups->deleteOrFail($group);
            [$method, $message] = ['success', I18N_OPERATION_OK];
        } elseif ($id <= 3) {
            $message = __d('me_cms', 'You cannot delete this users group');
        }
        $this->Flash->$method($message);

        return $this->redirectMatchingReferer(['action' => 'index']);
    }
}
