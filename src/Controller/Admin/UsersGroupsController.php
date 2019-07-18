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
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;

/**
 * UsersGroups controller
 * @property \MeCms\Model\Table\UsersGroupsTable $UsersGroups
 */
class UsersGroupsController extends AppController
{
    /**
     * Check if the provided user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     * @uses MeCms\Controller\Component\AuthComponent::isGroup()
     */
    public function isAuthorized($user = null)
    {
        //Only admins can access this controller
        return $this->Auth->isGroup('admin');
    }

    /**
     * Lists usersGroups
     * @return void
     */
    public function index()
    {
        $this->paginate['order'] = ['name' => 'ASC'];

        $groups = $this->paginate($this->UsersGroups->find());

        $this->set(compact('groups'));
    }

    /**
     * Adds users group
     * @return \Cake\Network\Response|null|void
     */
    public function add()
    {
        $group = $this->UsersGroups->newEntity();

        if ($this->getRequest()->is('post')) {
            $group = $this->UsersGroups->patchEntity($group, $this->getRequest()->getData());

            if ($this->UsersGroups->save($group)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('group'));
    }

    /**
     * Edits users group
     * @param string $id Users Group ID
     * @return \Cake\Network\Response|null|void
     */
    public function edit($id)
    {
        $group = $this->UsersGroups->get($id);

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $group = $this->UsersGroups->patchEntity($group, $this->getRequest()->getData());

            if ($this->UsersGroups->save($group)) {
                $this->Flash->success(I18N_OPERATION_OK);

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(I18N_OPERATION_NOT_OK);
        }

        $this->set(compact('group'));
    }

    /**
     * Deletes users group
     * @param string $id Users Group ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);

        $group = $this->UsersGroups->get($id);

        //Before deleting, checks if the group is a necessary group or if the group has some users
        if ($id > 3 && !$group->get('user_count')) {
            $this->UsersGroups->deleteOrFail($group);
            $this->Flash->success(I18N_OPERATION_OK);
        } else {
            $this->Flash->alert($id <= 3 ? __d('me_cms', 'You cannot delete this users group') : I18N_BEFORE_DELETE);
        }

        return $this->redirect(['action' => 'index']);
    }
}
