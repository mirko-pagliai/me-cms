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
App::uses('PhotoManager', 'MeCms.Utility');

/**
 * Photos Controller
 */
class PhotosController extends MeCmsAppController {
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAction()
	 * @uses MeAuthComponenet::isManager()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can delete photos
		if($this->Auth->isAction('delete'))
			return $this->Auth->isManager();
		
		return TRUE;
	}
	
	/**
	 * List photos
	 * @param string $albumId Photos album id
	 * @throws NotFoundException
	 */
	public function admin_index($albumId = NULL) {
		if(!$this->Photo->Album->exists($albumId))
			throw new NotFoundException(__d('me_cms', 'Invalid photos album'));
		
		$this->paginate = array(
			'conditions'	=> array('album_id' => $albumId),
			'fields'		=> array('id', 'album_id', 'filename'),
			'limit'			=> $this->config['photos_for_page']
		);
		
		$this->set(array(
			'photos'			=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Photos')
		));
	}

	/**
	 * Add photo from the tmp directory (`APP/tmp/uploads/photos`)
	 * @uses PhotoManager::getTmp()
	 * @uses PhotoManager::getTmpPath()
	 */
	public function admin_add() {
		//Gets albums
		$albums = $this->Photo->Album->find('list');
		
		//Checks for albums
		if(empty($albums)) {
			$this->Session->flash(__d('me_cms', 'Before you can add photos, you have to create at least an album'), 'error');
			$this->redirect(array('controller' => 'photos_albums', 'action' => 'index'));
		}
		
		//Gets the list of the files located in the temporary directory and sets the temporary directory path
		$tmpFiles = PhotoManager::getTmp();
		$tmpPath = PhotoManager::getTmpPath();
		
		//Checks for temporary files
		if(empty($tmpFiles)) {
			$this->Session->flash(__d('me_cms', 'There are no photos in the temporary directory %s', $tmpPath), 'error');
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
				$this->Session->flash(__d('me_cms', 'The photos have been saved'));
				$this->redirect(array('action' => 'index', $albumId));
			}
			else
				$this->Session->flash(__d('me_cms', 'The photos can not be saved. Please, try again'), 'error');
		}

		$this->set(am(array(
			'albumId'			=> empty($albumId) ? NULL : $albumId,
			'photos'			=> $tmpFiles,
			'title_for_layout'	=> __d('me_cms', 'Add photos'),
		), compact('albums', 'tmpPath')));
	}

	/**
	 * Edit photo
	 * @param string $id Photo ID
	 * @throws NotFoundException
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

		$this->set(am(array(
			'albums'			=> $this->Photo->Album->find('list'),
			'title_for_layout'	=> __d('me_cms', 'Edit photo')
		), compact('photo')));
	}

	/**
	 * Delete photo
	 * @param string $id Photo ID
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
		
		return $this->Photo->find('random', array(
			'conditions'	=> array('Album.active' => TRUE),
			'contain'		=> 'Album',
			'fields'		=> array('album_id', 'filename')
		));
	}
	
	/**
	 * View photo
	 * @param int $id Photo ID
	 * @throws NotFoundException
	 */
	public function view($id = NULL) {
		//Tries to get data from the cache
		$photo = Cache::read($cache = sprintf('photos_view_%s', $id), 'photos');
		
		//If the data are not available from the cache
        if(empty($photo)) {
			if(!$this->Photo->exists($id))
				throw new NotFoundException(__d('me_cms', 'Invalid photo'));

			$photo = $this->Photo->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('album_id', 'filename')
			));
			
            Cache::write($cache, $photo, 'photos');
		}
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Photo')), compact('photo')));
	}
}