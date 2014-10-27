<?php
/**
 * BannersPositionsController
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
 * BannersPositions Controller
 */
class BannersPositionsController extends MeCmsAppController {
	/**
	 * Checks if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAdmin()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isAdmin();
	}
	
	/**
	 * List banners positions
	 */
	public function admin_index() {
		$this->paginate = array(
			'fields'	=> array('id', 'name', 'description', 'banner_count'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'positions'			=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Banners positions')
		));
	}

	/**
	 * Add banners position
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->BannersPosition->create();
			if($this->BannersPosition->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The banners position has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The banners position could not be created. Please, try again'), 'error');
		}

		$this->set('title_for_layout', __d('me_cms', 'Add banners position'));
	}

	/**
	 * Edit banners position
	 * @param string $id Banners position ID
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->BannersPosition->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid banners position'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->BannersPosition->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The banners position has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The banners position could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->BannersPosition->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'name', 'description'))
			);

		$this->set('title_for_layout', __d('me_cms', 'Edit banners position'));
	}

	/**
	 * Delete banners position
	 * @param string $id Banners position ID
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->BannersPosition->id = $id;
		if(!$this->BannersPosition->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid banners position'));
			
		$this->request->onlyAllow('post', 'delete');
		
		//Before deleting, it checks if the position has some banners
		if(!$this->PostsCategory->field('banner_count')) {
			if($this->BannersPosition->delete())
				$this->Session->flash(__d('me_cms', 'The banners position has been deleted'));
			else
				$this->Session->flash(__d('me_cms', 'The banners position was not deleted'), 'error');
		}
		else
			$this->Session->flash(__d('me_cms', 'Before you delete this position, you have to delete its banners or assign them to another position'), 'error');
		
		$this->redirect(array('action' => 'index'));
	}
}