<?php
/**
 * UsersGroupsController
 *
 * This file is part of MeCms Backend
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Controller
 */

App::uses('MeCmsBackendAppController', 'MeCmsBackend.Controller');

/**
 * UsersGroups Controller
 */
class UsersGroupsController extends MeCmsBackendAppController {
	/**
	 * List users groups
	 */
	public function admin_index() {
		$this->paginate = array(
			'fields'	=> array('id', 'name', 'label', 'level', 'user_count'),
			'limit'		=> $this->config['site']['records_for_page']
		);
		
		$this->set(array(
			'usersGroups'		=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms_backend', 'Users groups')
		));
	}

	/**
	 * Add users group
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->UsersGroup->create();
			if($this->UsersGroup->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The users group has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The users group could not be created. Please, try again'), 'error');
		}

		$this->set('title_for_layout', __d('me_cms_backend', 'Add users group'));
	}

	/**
	 * Edit users group
	 * @param string $id Users group id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		//TO-DO: verificare non si stia modificando gruppo con ID 1-2-3
		if(!$this->UsersGroup->exists($id))
			throw new NotFoundException(__d('me_cms_backend', 'Invalid users group'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->UsersGroup->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The users group has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The users group could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->UsersGroup->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'name', 'label', 'description', 'level')
			));

		$this->set('title_for_layout', __d('me_cms_backend', 'Edit users group'));
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
			throw new NotFoundException(__d('me_cms_backend', 'Invalid users group'));
			
		$this->request->onlyAllow('post', 'delete');
				
		//Before deleting, it checks if the group is a necessary group or if the group has some users
		if($id > 3 && !$this->UsersGroup->field('user_count')) {
			if($this->UsersGroup->delete())
				$this->Session->flash(__d('me_cms_backend', 'The users group has been deleted'));
			else
				$this->Session->flash(__d('me_cms_backend', 'The users group was not deleted'), 'error');
		}
		elseif($id <= 3)
			$this->Session->flash(__d('me_cms_backend', 'You cannot delete this users group, because it\'s a necessary group'), 'error');
		else
			$this->Session->flash(__d('me_cms_backend', 'Before you delete this users group, you have to delete its users or assign them to another group'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}
}