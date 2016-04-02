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

use Cake\Network\Exception\InternalErrorException;
use MeCms\Controller\AppController;

/**
 * Photos controller
 * @property \MeCms\Model\Table\PhotosTable $Photos
 */
class PhotosController extends AppController {	
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeCms\Model\Table\PhotosAlbums::getList()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
        
		//Checks if the folder and its subfolders are writeable
		if(!folder_is_writeable(PHOTOS))
			throw new InternalErrorException(__d('me_tools', 'File or directory {0} not writeable', rtr(PHOTOS)));
		
		if($this->request->isAction(['index', 'edit', 'upload'])) {
			//Gets albums
            $albums = $this->Photos->Albums->getList();
			
			//Checks for albums
			if(empty($albums) && !$this->request->isAction('index')) {
				$this->Flash->alert(__d('me_cms', 'Before you can manage photos, you have to create at least an album'));
				return $this->redirect(['controller' => 'PhotosAlbums', 'action' => 'index']);
			}
            
            $this->set(compact('albums'));
		}
		
		//See http://book.cakephp.org/2.0/en/core-libraries/components/security-component.html#disabling-csrf-and-post-data-validation-for-specific-actions
		$this->Security->config('unlockedActions', 'upload');
	}
	
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {		
		//Only admins and managers can delete photos
		if($this->request->isAction('delete'))
			return $this->Auth->isGroup(['admin', 'manager']);
				
		return TRUE;
	}
	
	/**
	 * Uploads photos
	 * @uses MeCms\Controller\_upload()
	 */
	public function upload() {
		//If there's only one album, it automatically sets the query value
		if(!$this->request->query('album') && count($this->viewVars['albums']) < 2)
			$this->request->query['album'] = fk($this->viewVars['albums']);
		
		$album = $this->request->query('album');
		
		if($album && $this->request->data('file')) {
            //Uploads
            $filename = $this->_upload($this->request->data('file'), PHOTOS.DS.$album);
                
			//Checks if the file has been uploaded
			if($filename) {
				$photo = $this->Photos->save($this->Photos->newEntity([
					'album_id'	=> $album,
					'filename'	=> basename($filename)
				]));
				
				if($photo->id)
					$this->set('edit_url', ['action' => 'edit', $photo->id]);
			}
			
			//Renders the element `backend/uploader/response`
			$this->render('/Element/backend/uploader/response', FALSE);
		}
	}

    /**
     * Edits photo
     * @param string $id Photo ID
     */
    public function edit($id = NULL)  {
        $photo = $this->Photos->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $photo = $this->Photos->patchEntity($photo, $this->request->data);
			
            if($this->Photos->save($photo)) {
                $this->Flash->success(__d('me_cms', 'The photo has been saved'));
                return $this->redirect(['action' => 'index', $photo->album_id]);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The photo could not be saved'));
        }

        $this->set(compact('photo'));
    }
	
    /**
     * Deletes photo
     * @param string $id Photo ID
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $photo = $this->Photos->get($id);
		
        if($this->Photos->delete($photo))
            $this->Flash->success(__d('me_cms', 'The photo has been deleted'));
        else
            $this->Flash->error(__d('me_cms', 'The photo could not be deleted'));
			
        return $this->redirect(['action' => 'index', $photo->album_id]);
    }
}