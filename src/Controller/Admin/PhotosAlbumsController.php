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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;

/**
 * PhotosAlbums controller
 * @property \MeCms\Model\Table\PhotosAlbumsTable $PhotosAlbums
 */
class PhotosAlbumsController extends AppController {
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {		
		//Only admins can delete albums
		if($this->request->isAction('delete')) {
			return $this->Auth->isGroup('admin');
        }
        
		return TRUE;
	}
	
	/**
     * Lists albums
     */
    public function index() {
		$this->paginate['order'] = ['title' => 'ASC'];
		
        $albums = $this->paginate(
			$this->PhotosAlbums->find()
                ->select(['id', 'slug', 'title', 'photo_count', 'active'])
		);
        
		$this->set(compact('albums'));
    }

    /**
     * Adds photos album
     */
    public function add() {		
        $album = $this->PhotosAlbums->newEntity();
		
        if($this->request->is('post')) {
            $album = $this->PhotosAlbums->patchEntity($album, $this->request->data);
			
            if($this->PhotosAlbums->save($album)) {
                $this->Flash->success(__d('me_cms', 'The photos album has been saved'));
				return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The photos album could not be saved'));
            }
        }

        $this->set(compact('album'));
    }

    /**
     * Edits photos album
     * @param string $id Photos Album ID
     */
    public function edit($id = NULL)  {
        $album = $this->PhotosAlbums->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $album = $this->PhotosAlbums->patchEntity($album, $this->request->data);
			
            if($this->PhotosAlbums->save($album)) {
                $this->Flash->success(__d('me_cms', 'The photos album has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The photos album could not be saved'));
            }
        }

        $this->set(compact('album'));
    }
    /**
     * Deletes photos album
     * @param string $id Photos Album ID
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $album = $this->PhotosAlbums->get($id);
		
		//Before deleting, it checks if the album has some photos
		if(!$album->photo_count) {
			if($this->PhotosAlbums->delete($album)) {
				$this->Flash->success(__d('me_cms', 'The photos album has been deleted'));
            }
            else {
				$this->Flash->error(__d('me_cms', 'The photos album could not be deleted'));
            }
		}
		else {
			$this->Flash->alert(__d('me_cms', 'Before you delete this album, you have to delete its photos or assign them to another album'));
        }
        
        return $this->redirect(['action' => 'index']);
    }
}