<?php
/**
 * UsersGroupsController
 *
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');

/**
 * UsersGroups Controller
 */
class UsersGroupsController extends MeCmsAppController {
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAdmin()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isAdmin();
	}
	
	/**
	 * List users groups
	 */
	public function admin_index() {
		$this->paginate = array(
			'fields'	=> array('id', 'name', 'label', 'user_count'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'usersGroups'		=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Users groups')
		));
	}

	/**
	 * Add users group
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->UsersGroup->create();
			if($this->UsersGroup->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The users group has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The users group could not be created. Please, try again'), 'error');
		}

		$this->set('title_for_layout', __d('me_cms', 'Add users group'));
	}

	/**
	 * Edit users group
	 * @param string $id Users group id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		//TO-DO: verificare non si stia modificando gruppo con ID 1-2-3
		if(!$this->UsersGroup->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid users group'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->UsersGroup->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The users group has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The users group could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->UsersGroup->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'name', 'label', 'description')
			));

		$this->set('title_for_layout', __d('me_cms', 'Edit users group'));
	}

	/**
	 * Delete users group
	 * @param string $id Users group id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		//TO-DO: verificare non si stia modificando gruppo con ID 1-2-3
		$this->UsersGroup->id = $id;
		if(!$this->UsersGroup->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid users group'));
			
		$this->request->onlyAllow('post', 'delete');
				
		//Before deleting, it checks if the group is a necessary group or if the group has some users
		if($id > 3 && !$this->UsersGroup->field('user_count')) {
			if($this->UsersGroup->delete())
				$this->Session->flash(__d('me_cms', 'The users group has been deleted'));
			else
				$this->Session->flash(__d('me_cms', 'The users group was not deleted'), 'error');
		}
		elseif($id <= 3)
			$this->Session->flash(__d('me_cms', 'You cannot delete this users group, because it\'s a necessary group'), 'error');
		else
			$this->Session->flash(__d('me_cms', 'Before you delete this users group, you have to delete its users or assign them to another group'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}
}