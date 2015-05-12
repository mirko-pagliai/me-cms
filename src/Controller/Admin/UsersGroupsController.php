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
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;

/**
 * UsersGroups controller
 * @property \MeCms\Model\Table\UsersGroupsTable $UsersGroups
 */
class UsersGroupsController extends AppController {
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\AppController::isAuthorized()
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isGroup('admin');
	}
	
	/**
     * Lists usersGroups
     */
    public function index() {		
		$this->set('groups', $this->paginate(
			$this->UsersGroups->find()
				->select(['id', 'name', 'label', 'user_count'])
				->order(['UsersGroups.name' => 'ASC'])
		));
    }

    /**
     * Adds users group
     */
    public function add() {
        $group = $this->UsersGroups->newEntity();
		
        if($this->request->is('post')) {
            $group = $this->UsersGroups->patchEntity($group, $this->request->data);
			
            if($this->UsersGroups->save($group)) {
                $this->Flash->success(__d('me_cms', 'The users group has been saved'));
				return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The users group could not be saved'));
        }

        $this->set(compact('group'));
    }

    /**
     * Edits users group
     * @param string $id Users Group ID
     * @throws \Cake\Network\Exception\NotFoundException
     */
    public function edit($id = NULL)  {
        $group = $this->UsersGroups->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $group = $this->UsersGroups->patchEntity($group, $this->request->data);
			
            if($this->UsersGroups->save($group)) {
                $this->Flash->success(__d('me_cms', 'The users group has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The users group could not be saved'));
        }

        $this->set(compact('group'));
    }
    /**
     * Deletes users group
     * @param string $id Users Group ID
     * @throws \Cake\Network\Exception\NotFoundException
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $group = $this->UsersGroups->get($id);
		
		//Before deleting, checks if the group is a necessary group or if the group has some users
		if($id > 3 && !$group->user_count) {
			if($this->UsersGroups->delete($group))
				$this->Flash->success(__d('me_cms', 'The users group has been deleted'));
			else
				$this->Flash->error(__d('me_cms', 'The users group could not be deleted'));
		}
		elseif($id <= 3)
			$this->Flash->alert(__d('me_cms', 'You cannot delete this users group, because it\'s a necessary group'));
		else
			$this->Flash->alert(__d('me_cms', 'Before you delete this users group, you have to delete its users or assign them to another group'));
			
        return $this->redirect(['action' => 'index']);
    }
}