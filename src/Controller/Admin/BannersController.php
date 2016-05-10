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
 * Banners controller
 * @property \MeCms\Model\Table\BannersTable $Banners
 */
class BannersController extends AppController {
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 * @uses MeCms\Model\Table\BannersPositions::getList()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		if($this->request->isAction(['index', 'edit', 'upload'])) {
			//Gets positions
			$positions = $this->Banners->Positions->getList();
		
			//Checks for positions
			if(empty($positions) && !$this->request->isAction('index')) {
				$this->Flash->alert(__d('me_cms', 'Before you can manage banners, you have to create at least a banner position'));
				return $this->redirect(['controller' => 'BannersPositions', 'action' => 'index']);
			}
            
            $this->set(compact('positions'));
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
		//Only admins can delete banners
		if($this->request->isAction('delete'))
			return $this->Auth->isGroup('admin');
		
		//Admins and managers can access other actions
		return $this->Auth->isGroup(['admin', 'manager']);
	}
	
	/**
     * Lists banners.
     * 
     * This action can use the `index_as_grid` template.
	 * @uses MeCms\Model\Table\BannersTable::queryFromFilter()
     */
    public function index() {
        $render = $this->request->query('render');
        
        if($this->Cookie->read('render.banners') === 'grid' && !$render) {
            return $this->redirect(['?' => am($this->request->query, ['render' => 'grid'])]);
        }
        
		$query = $this->Banners->find()
			->contain(['Positions' => ['fields' => ['id', 'name']]])
			->select(['id', 'filename', 'target', 'description', 'active', 'click_count', 'created']);
		
		$this->paginate['order'] = ['Banners.created' => 'DESC'];
		$this->paginate['sortWhitelist'] = ['Banners.filename', 'Positions.name', 'description', 'click_count', 'Banners.created'];
		
		$this->set('banners', $this->paginate($this->Banners->queryFromFilter($query, $this->request->query)));
        
        if($render) {
            $this->Cookie->write('render.banners', $render);
            
            if($render === 'grid') {
                $this->paginate['limit'] = $this->paginate['maxLimit'] = config('backend.photos');
                
                $this->render('index_as_grid');
            }
        }
    }
	
	/**
	 * Uploads banners
	 * @uses MeCms\Controller\_upload()
	 */
	public function upload() {
		//If there's only one position, it automatically sets the query value
		if(!$this->request->query('position') && count($this->viewVars['positions']) < 2)
			$this->request->query['position'] = fk($this->viewVars['positions']);
				
		$position = $this->request->query('position');
		
		if($position && $this->request->data('file')) {
            //Uploads
            $filename = $this->_upload($this->request->data('file'), BANNERS);
            
			//Checks if the file has been uploaded
			if($filename) {
				$banner = $this->Banners->save($this->Banners->newEntity([
					'position_id'	=> $position,
					'filename'		=> basename($filename)
				]));
				
				if($banner->id)
					$this->set('edit_url', ['action' => 'edit', $banner->id]);
			}
			
			//Renders the element `backend/uploader/response`
			$this->render('/Element/backend/uploader/response', FALSE);
		}
	}

    /**
     * Edits banner
     * @param string $id Banner ID
     */
    public function edit($id = NULL)  {
        $banner = $this->Banners->get($id);
		
        if($this->request->is(['patch', 'post', 'put'])) {
            $banner = $this->Banners->patchEntity($banner, $this->request->data);
			
            if($this->Banners->save($banner)) {
                $this->Flash->success(__d('me_cms', 'The banner has been saved'));
                return $this->redirect(['action' => 'index']);
            } 
			else
                $this->Flash->error(__d('me_cms', 'The banner could not be saved'));
        }

        $this->set(compact('banner'));
    }
    
    /**
     * Downloads banner
     * @param string $id Banner ID
     * @uses MeCms\Controller\AppController::_download()
     */
    public function download($id = NULL) {
        $banner = $this->Banners->get($id);
        
        return $this->_download($banner->path);
    }
    
    /**
     * Deletes banner
     * @param string $id Banner ID
     */
    public function delete($id = NULL) {
        $this->request->allowMethod(['post', 'delete']);
		
        $banner = $this->Banners->get($id);
		
        if($this->Banners->delete($banner))
            $this->Flash->success(__d('me_cms', 'The banner has been deleted'));
        else
            $this->Flash->error(__d('me_cms', 'The banner could not be deleted'));
			
        return $this->redirect(['action' => 'index']);
    }
}