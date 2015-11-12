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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller\Admin;

use MeCms\Controller\AppController;
use MeCms\Utility\BannerFile;

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
	 * @uses MeCms\Utility\BannerFile::check()
	 * @uses MeCms\Utility\BannerFile::folder()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		
		//Checks if the main folder and its subfolders are writable
		if(!BannerFile::check()) {
			$this->Flash->error(__d('me_cms', 'The directory {0} is not readable or writable', rtr(BannerFile::folder())));
			$this->redirect(['_name' => 'dashboard']);
		}
		
		if($this->request->isAction(['index', 'edit', 'upload'])) {
			//Gets and sets positions
			$this->set('positions', $positions = $this->Banners->Positions->getList());
		
			//Checks for positions
			if(empty($positions) && !$this->request->isAction('index')) {
				$this->Flash->alert(__d('me_cms', 'Before you can manage banners, you have to create at least a banner position'));
				$this->redirect(['controller' => 'BannersPositions', 'action' => 'index']);
			}
		}
		
		//See http://book.cakephp.org/2.0/en/core-libraries/components/security-component.html#disabling-csrf-and-post-data-validation-for-specific-actions
		$this->Security->config('unlockedActions', 'upload');
	}
	
	/**
	 * Check if the provided user is authorized for the request
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used
	 * @return bool TRUE if the user is authorized, otherwise FALSE
	 * @uses MeCms\Controller\AppController::isAuthorized()
	 * @uses MeCms\Controller\Component\AuthComponent::isGroup()
	 * @uses MeTools\Network\Request::isAction()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can delete banners
		if($this->request->isAction('delete'))
			return $this->Auth->isGroup('admin');
		
		//Admins and managers can access other actions
		return parent::isAuthorized($user);
	}
	
	/**
     * Lists banners
	 * @uses MeCms\Model\Table\BannersTable::queryFromFilter()
     */
    public function index() {
		$query = $this->Banners->find()
			->contain(['Positions' => ['fields' => ['id', 'name']]])
			->select(['id', 'filename', 'target', 'description', 'active', 'click_count']);
		
		$this->paginate = [
			'order'			=> ['Banners.filename' => 'ASC'],
			'sortWhitelist'	=> ['filename', 'Positions.name', 'description', 'click_count']
		];
		
		$this->set('banners', $this->paginate($this->Banners->queryFromFilter($query, $this->request->query)));
    }
	
	/**
	 * Uploads banners
	 * @uses MeCms\Controller\_upload()
	 * @uses MeCms\Utility\BannerFile::folder()
	 */
	public function upload() {
		//If there's only one position, it automatically sets the query value
		if(!$this->request->query('position') && count($this->viewVars['positions']) < 2)
			$this->request->query['position'] = fk($this->viewVars['positions']);
		
		$position = $this->request->query('position');
		
		if($position && $this->request->data('file'))
			//Checks if the file has been uploaded
			if($filename = $this->_upload($this->request->data('file'), BannerFile::folder()))
				$this->Banners->save($this->Banners->newEntity([
					'position_id'	=> $position,
					'filename'		=> basename($filename)
				]));
	}

    /**
     * Edits banner
     * @param string $id Banner ID
     * @throws \Cake\Network\Exception\NotFoundException
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
     * Deletes banner
     * @param string $id Banner ID
     * @throws \Cake\Network\Exception\NotFoundException
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