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
use MeCms\Utility\PhotoFile;

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
	 * @uses MeCms\Utility\PhotoFile::check()
	 * @uses MeCms\Utility\PhotoFile::folder()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		//Checks if the main folder and its subfolders are writable
		if(!PhotoFile::check()) {
			$this->Flash->error(__d('me_tools', 'File or directory `{0}` not writeable', rtr(PhotoFile::folder())));
			$this->redirect(['_name' => 'dashboard']);
		}
		
		if($this->request->isAction(['index', 'edit', 'upload'])) {
			//Gets and sets albums
			$this->set('albums', $albums = $this->Photos->Albums->getList());
			
			//Checks for albums
			if(empty($albums) && !$this->request->isAction('index')) {
				$this->Flash->alert(__d('me_cms', 'Before you can manage photos, you have to create at least an album'));
				$this->redirect(['controller' => 'PhotosAlbums', 'action' => 'index']);
			}
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
			$this->Auth->isGroup(['admin', 'manager']);
				
		return TRUE;
	}
	
	/**
     * Lists photos
	 * @param string $album_id Album ID
	 * @throws \Cake\Network\Exception\NotFoundException
	 */
    public function index($album_id = NULL) {
		if(empty($album_id))
			throw new \Cake\Network\Exception\NotFoundException(__d('me_cms', 'The album ID is missing'));
		
		$this->paginate['limit'] = $this->paginate['maxLimit'] = config('backend.photos');
		$this->paginate['order'] = ['filename' => 'ASC'];
		
		$this->set('photos', $this->paginate(
			$this->Photos->find()
				->select(['id', 'album_id', 'filename'])
				->where(compact('album_id'))
		));
		
		$this->set(compact('album_id'));
    }
	
	/**
	 * Uploads photos
	 * @uses MeCms\Controller\_upload()
	 * @uses MeCms\Utility\PhotoFile::folder()
	 */
	public function upload() {
		//If there's only one album, it automatically sets the query value
		if(!$this->request->query('album') && count($this->viewVars['albums']) < 2)
			$this->request->query['album'] = fk($this->viewVars['albums']);
		
		$album = $this->request->query('album');
		
		if($album && $this->request->data('file'))
			//Checks if the file has been uploaded
			if($filename = $this->_upload($this->request->data('file'), PhotoFile::folder($album)))
				$this->Photos->save($this->Photos->newEntity([
					'album_id'	=> $album,
					'filename'	=> basename($filename)
				]));
	}

    /**
     * Edits photo
     * @param string $id Photo ID
     * @throws \Cake\Network\Exception\NotFoundException
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
     * @throws \Cake\Network\Exception\NotFoundException
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