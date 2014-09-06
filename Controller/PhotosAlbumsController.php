<?php
/**
 * PhotosAlbumsController
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
 * PhotosAlbums Controller
 */
class PhotosAlbumsController extends MeCmsAppController {
	/**
	 * List photos albums
	 */
	public function admin_index() {
		$this->paginate = array(
			'fields'	=> array('id', 'slug', 'title', 'photo_count'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'photosAlbums'		=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Photos albums')
		));
	}

	/**
	 * Add photos album
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->PhotosAlbum->create();
			if($this->PhotosAlbum->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The photos album has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The photos album could not be created. Please, try again'), 'error');
		}

		$this->set('title_for_layout', __d('me_cms', 'Add photos album'));
	}

	/**
	 * Edit photos album
	 * @param string $id Photos album id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->PhotosAlbum->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid photos album'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->PhotosAlbum->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The photos album has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The photos album could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->PhotosAlbum->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'title', 'slug', 'description')
			));

		$this->set('title_for_layout', __d('me_cms', 'Edit photos album'));
	}

	/**
	 * Delete photos album
	 * @param string $id Photos album id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->PhotosAlbum->id = $id;
		if(!$this->PhotosAlbum->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid photos album'));
			
		$this->request->onlyAllow('post', 'delete');
		
		//Before deleting, it checks if the album has some photos
		if(!$this->PhotosAlbum->field('photo_count')) {
			if($this->PhotosAlbum->delete())
				$this->Session->flash(__d('me_cms', 'The photos album has been deleted'));
			else
				$this->Session->flash(__d('me_cms', 'The photos album was not deleted'), 'error');
		}
		else
			$this->Session->flash(__d('me_cms', 'Before you delete this album, you have to delete its photos or assign them to another album'), 'error');
					
		$this->redirect(array('action' => 'index'));
	}
}