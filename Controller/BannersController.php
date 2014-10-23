<?php
/**
 * BannersController
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
 * Banners Controller
 */
class BannersController extends MeCmsAppController {
	/**
	 * List banners
	 */
	public function admin_index() {
		$this->paginate = array(
			'contain'	=> 'Position.name',
			'fields'	=> array('id', 'filename', 'url', 'description', 'active'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'banners'			=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Banners')
		));
	}

	/**
	 * View banner
	 * @param string $id Banner ID
	 * @throws NotFoundException
	 */
	public function admin_view($id = NULL) {
		if(!$this->Banner->exists($id))
			throw new NotFoundException( __d('me_cms', 'Invalid banner'));
		
		//Gets the banner
		$banner = $this->Banner->find('first', array(
			'conditions'	=> array('Banner.id' => $id),
			'contain'		=> 'Position.name',
			'fields'		=> array('id', 'filename', 'url', 'description', 'active')
		));
		
		debug($banner);
		
		$this->set(array(
			'banner'			=> $banner,
			'title_for_layout'	=> __d('me_cms', 'View banner')
		));
	}

	/**
	 * Add banner
	 */
	public function admin_add() {
		//Gets the positions
		$positions = $this->Banner->Position->find('list');
		
		//Checks for positions
		if(empty($positions)) {
			$this->Session->flash(__d('me_cms', 'Before you can add a banner, you have to create at least a banner position'), 'error');
			$this->redirect(array('controller' => 'banners_positions', 'action' => 'index'));
		}
		
		if($this->request->is('post')) {
			$this->Banner->create();
			if($this->Banner->save($this->request->data)) {
				$this->Session->flash( __d('me_cms', 'The banner has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash( __d('me_cms', 'The banner could not be created. Please, try again'), 'error');
		}

		$this->set('positions', $positions);
		$this->set('title_for_layout',  __d('me_cms', 'Add banner'));
	}

	/**
	 * Edit banner
	 * @param string $id Banner id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Banner->exists($id))
			throw new NotFoundException( __d('me_cms', 'Invalid banner'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->Banner->save($this->request->data)) {
				$this->Session->flash( __d('me_cms', 'The banner has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash( __d('me_cms', 'The banner could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->Banner->find('first', array(
				'conditions' => array('Banner.'.$this->Banner->primaryKey => $id)
			));

		$this->set('positions', $this->Banner->Position->find('list'));
		$this->set('title_for_layout',  __d('me_cms', 'Edit banner'));
	}

	/**
	 * Delete banner
	 * @param string $id Banner id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Banner->id = $id;
		if(!$this->Banner->exists())
			throw new NotFoundException( __d('me_cms', 'Invalid banner'));
			
		$this->request->onlyAllow('post', 'delete');
		
		if($this->Banner->delete())
			$this->Session->flash( __d('me_cms', 'The banner has been deleted'));
		else
			$this->Session->flash( __d('me_cms', 'The banner was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}
}