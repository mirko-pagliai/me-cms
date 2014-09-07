<?php
/**
 * PhotosController
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
App::uses('Album', 'MeCms.Utility');

/**
 * Photos Controller
 */
class PhotosController extends MeCmsAppController {
	/**
	 * List photos
	 * @param string $albumId Photos album id
	 * @throws NotFoundException
	 * @uses Album::getAlbumPath() to get the album path
	 */
	public function admin_index($albumId = NULL) {
		if(!$this->Photo->Album->exists($albumId))
			throw new NotFoundException(__d('me_cms', 'Invalid photos album'));
		
		$this->paginate = array(
			'conditions'	=> array('album_id' => $albumId),
			'fields'		=> array('id', 'filename', 'description'),
			'limit'			=> $this->config['photos_for_page']
		);
		
		$this->set(array(
			'path'				=> Album::getAlbumPath($albumId),
			'photos'			=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Photos')
		));
	}

	/**
	 * Add photo from the tmp directory (`APP/tmp/photos`)
	 * @uses Album::getTmp() to get the list of the photos in the temporary directory
	 * @uses Album::getTmpPath() to get the path of the temporary directory
	 */
	public function admin_add() {
		//Gets albums
		$albums = $this->Photo->Album->find('list');
		
		//Checks for albums
		if(empty($albums)) {
			$this->Session->flash(__d('me_cms', 'Before you can add photos, you have to create at least an album'), 'error');
			$this->redirect(array('controller' => 'photos_albums', 'action' => 'index'));
		}
		
		//Gets the list of the photos located in the tmp directory
		$tmpPhotos = Album::getTmp();
		
		//Checks for photos
		if(empty($tmpPhotos)) {
			$this->Session->flash(__d('me_cms', 'There is no photo in the temporary directory %s', Album::getTmpPath()), 'error');
			$this->redirect(array('controller' => 'photos_albums', 'action' => 'index'));
		}
		
		if($this->request->is('post')) {
			//Saves the album id
			$albumId = $this->request->data['Photo']['album_id'];
			unset($this->request->data['Photo']['album_id']);
			
			//Removes all unselected elements and adds the album id to the valid elements
			foreach($this->request->data['Photo'] as $k => $photo) {
				if(empty($photo['filename'])) {
					unset($this->request->data['Photo'][$k]);
					continue;
				}
				
				$this->request->data['Photo'][$k]['album_id'] = $albumId;
			}
			
			$this->Photo->create();
			if($this->Photo->saveMany(array_filter($this->request->data['Photo']))) {
				$this->Session->flash(__d('me_cms', 'The photos has been saved'));
				$this->redirect(array('action' => 'index', $albumId));
			}
			else
				$this->Session->flash(__d('me_cms', 'The photos could not be saved. Please, try again'), 'error');
		}

		$this->set(array(
			'albumId'			=> empty($albumId) ? NULL : $albumId,
			'albums'			=> $albums,
			'photos'			=> $tmpPhotos,
			'title_for_layout'	=> __d('me_cms', 'Add photos'),
			'tmpPath'			=> Album::getTmpPath()
		));
	}

	/**
	 * Edit photo
	 * @param string $id Photo id
	 * @throws NotFoundException
	 * @uses Album::getAlbumPath() to get the album path
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Photo->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid photo'));
		
		//Gets the photo
		$photo = $this->Photo->find('first', array(
			'conditions'	=> array('id' => $id),
			'fields'		=> array('id', 'album_id', 'filename', 'description')
		));
		
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->Photo->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The photo has been edited'));
				$this->redirect(array('action' => 'index', $photo['Photo']['album_id']));
			}
			else
				$this->Session->flash(__d('me_cms', 'The photo could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $photo;

		$this->set(array(
			'albumPath'			=> Album::getAlbumPath($photo['Photo']['album_id']),
			'albums'			=> $this->Photo->Album->find('list'),
			'photo'				=> $photo['Photo']['filename'],
			'title_for_layout'	=> __d('me_cms', 'Edit photo')
		));
	}

	/**
	 * Delete photo
	 * @param string $id Photo id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Photo->id = $id;
		if(!$this->Photo->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid photo'));
			
		$this->request->onlyAllow('post', 'delete');
		
		$albumId = $this->Photo->field('album_id', array('id' => $id));
		
		if($this->Photo->delete())
			$this->Session->flash(__d('me_cms', 'The photo has been deleted'));
		else
			$this->Session->flash(__d('me_cms', 'The photo was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index', $albumId));
	}
	
	/**
	 * Gets a random photo.
	 * This method works only with `requestAction()`.
	 * @return array Photo
	 * @throws ForbiddenException
	 */
	public function request_random() {
		//This method works only with "requestAction()"
		if(empty($this->request->params['requested']))
            throw new ForbiddenException();
		
		//Gets the photo
		$photo = $this->Photo->find('random', array('fields' => array('album_id', 'filename')));
		
		//Adds the full path
		$photo['Photo']['path'] = Album::getAlbumPath($photo['Photo']['album_id']).DS.$photo['Photo']['filename'];
		
		return $photo;
	}
}