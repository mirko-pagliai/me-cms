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
		
		if($this->request->isAction(['index', 'edit', 'upload'])) {
			//Gets albums
            $albums = $this->Photos->Albums->getList();
			
			//Checks for albums
			if(empty($albums) && !$this->request->isAction('index')) {
				$this->Flash->alert(__d('me_cms', 'You must first create an album'));
				return $this->redirect(['controller' => 'PhotosAlbums', 'action' => 'index']);
			}
            
            $this->set(compact('albums'));
		}
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
		if($this->request->isAction('delete')) {
			return $this->Auth->isGroup(['admin', 'manager']);
        }
        
		return TRUE;
	}
    
    /**
     * Lists photos.
     * 
     * This action can use the `index_as_grid` template.
	 * @uses MeCms\Model\Table\PhotosTable::queryFromFilter()
     */
    public function index() {
        $render = $this->request->query('render');
        
        if($this->Cookie->read('render.photos') === 'grid' && !$render) {
            return $this->redirect(['?' => am($this->request->query, ['render' => 'grid'])]);
        }
        
		$query = $this->Photos->find()
            ->select(['id', 'album_id', 'filename', 'active', 'description', 'created'])
			->contain([
                'Albums' => function($q) {
                    return $q->select(['id', 'slug', 'title']);
                },
            ]);
		
		$this->paginate['order'] = [
            sprintf('%s.created', $this->Photos->alias()) => 'DESC',
            sprintf('%s.id', $this->Photos->alias()) => 'DESC',
        ];
		$this->paginate['sortWhitelist'] = ['filename', 'Albums.title', 'Photos.created'];
		
		//Sets the paginate limit and the maximum paginate limit
		//See http://book.cakephp.org/3.0/en/controllers/components/pagination.html#limit-the-maximum-number-of-rows-that-can-be-fetched
        if($render === 'grid') {
            $this->paginate['limit'] = $this->paginate['maxLimit'] = config('admin.photos');
        }
		
		$this->set('photos', $this->paginate($this->Photos->queryFromFilter($query, $this->request->query)));
        
        if($render) {
            $this->Cookie->write('render.photos', $render);
            
            if($render === 'grid') {
                $this->render('index_as_grid');
            }
        }
    }
	
	/**
	 * Uploads photos
     * @uses MeTools\Controller\Component\UploaderComponent::error()
     * @uses MeTools\Controller\Component\UploaderComponent::mimetype()
     * @uses MeTools\Controller\Component\UploaderComponent::save()
     * @uses MeTools\Controller\Component\UploaderComponent::set()
	 */
	public function upload() {
        $album = $this->request->query('album');
            
		//If there's only one album, it automatically sets the query value
		if(!$album && count($this->viewVars['albums']) < 2) {
			$this->request->query['album'] = fk($this->viewVars['albums']);
        }
        
        if($this->request->data('file')) {
            if(empty($album)) {
				throw new InternalErrorException(__d('me_cms', 'Missing album ID'));
            }
            
            http_response_code(500);
            
            $uploaded = $this->Uploader->set($this->request->data('file'))
                ->mimetype('image')
                ->save(PHOTOS.DS.$album);
            
            if(!$uploaded) {
                exit($this->Uploader->error());
            }
            
            $saved = $this->Photos->save($this->Photos->newEntity([
                'album_id' => $album,
                'filename' => basename($uploaded),
            ]));
            
            if(!$saved) {
                exit(__d('me_cms', 'The photo could not be saved'));
            }
            
            http_response_code(200);
            
            $this->render(FALSE);            
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
                $this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
                return $this->redirect(['action' => 'index', $photo->album_id]);
            } 
			else {
                $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
            }
        }

        $this->set(compact('photo'));
    }
    
    /**
     * Downloads photo
     * @param string $id Photo ID
     * @uses MeCms\Controller\AppController::_download()
     */
    public function download($id = NULL) {        
        return $this->_download($this->Photos->get($id)->path);
    }
	
    /**
     * Deletes photo
     * @param string $id Photo ID
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $photo = $this->Photos->get($id);
		
        if($this->Photos->delete($photo)) {
			$this->Flash->success(__d('me_cms', 'The operation has been performed correctly'));
        }
        else {
            $this->Flash->error(__d('me_cms', 'The operation has not been performed correctly'));
        }
			
        return $this->redirect(['action' => 'index', $photo->album_id]);
    }
}