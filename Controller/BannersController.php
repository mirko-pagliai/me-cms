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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\Controller
 */

App::uses('MeCmsAppController', 'MeCms.Controller');
App::uses('BannerManager', 'MeCms.Utility');

/**
 * Banners Controller
 */
class BannersController extends MeCmsAppController {
	/**
	 * Checks if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponent::isManager()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can access this controller
		return $this->Auth->isManager();
	}
	
	/**
	 * Add banner
	 * @uses BannerManager::getTmp()
	 * @uses BannerManager::getTmpPath()
	 */
	public function admin_add() {
		//Gets the positions
		$positions = $this->Banner->Position->find('list');
		
		//Checks for positions
		if(empty($positions)) {
			$this->Session->flash(__d('me_cms', 'Before you can add a banner, you have to create at least a banner position'), 'alert');
			$this->redirect(array('controller' => 'banners_positions', 'action' => 'index'));
		}
		
		//Gets the temporary files
		$tmpFiles = BannerManager::getTmp();
		
		//Checks for temporary files
		if(empty($tmpFiles)) {
			$this->Session->flash(__d('me_cms', 'Before you can add a banner, you have to upload a banner'), 'alert');
			$this->redirect(array('action' => 'upload'));
		}
		
		//Sets values as keys
		$tmpFiles = array_combine($tmpFiles, $tmpFiles);
		
		//If the file to be used has been specified
		if(!empty($this->request->query['file'])) {
			if($this->request->is('post')) {
				$this->Banner->create();
				if($this->Banner->save($this->request->data)) {
					$this->Session->flash(__d('me_cms', 'The banner has been saved'));
					$this->redirect(array('action' => 'index'));
				}
				else
					$this->Session->flash(__d('me_cms', 'The banner could not be saved. Please, try again'), 'error');
			}
			
			//Sets the filename and the full path for the temporary file
			$tmpFile = array(
				'filename'	=> $tmpFiles[$this->request->query['file']],
				'path'		=> BannerManager::getTmpPath().DS.$tmpFiles[$this->request->query['file']]
			);
			
			$this->set(compact('tmpFile'));
		}
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Add banner')), compact('positions', 'tmpFiles')));
	}

	/**
	 * Delete banner
	 * @param string $id Banner ID
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Banner->id = $id;
		if(!$this->Banner->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		$this->request->onlyAllow('post', 'delete');
		
		if($this->Banner->delete())
			$this->Session->flash(__d('me_cms', 'The banner has been deleted'));
		else
			$this->Session->flash(__d('me_cms', 'The banner was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}

	/**
	 * Edit banner
	 * @param string $id Banner ID
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Banner->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->Banner->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The banner has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The banner could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->Banner->findById($id, array('id', 'position_id', 'filename', 'target', 'description', 'active'));

		$this->set(array(
			'positions'			=> $this->Banner->Position->find('list'),
			'title_for_layout'	=> __d('me_cms', 'Edit banner')
		));
	}
	
	/**
	 * List banners
	 * @uses Banner::conditionsFromFilter()
	 */
	public function admin_index() {
		//Sets conditions from the filter form
		$conditions = empty($this->request->query) ? array() : $this->Banner->conditionsFromFilter($this->request->query);
		
		$this->paginate = am(array(
			'contain'	=> 'Position.name',
			'fields'	=> array('id', 'filename', 'target', 'description', 'active', 'click_count'),
			'limit'		=> $this->config['backend']['records'],
			'order'		=> array('Banner.filename' => 'ASC')
		), compact('conditions'));
		
		//Tries to get data from the cache
		$positions = Cache::read($cache = 'admin_positions_list', 'banners');
		
		//If the data are not available from the cache
        if(empty($positions))
            Cache::write($cache, $positions = $this->Banner->Position->find('list'), 'banners');
		
		$this->set(am(array(
			'banners'			=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Banners')
		), compact('positions')));
	}

	/**
	 * Upload banner
	 * @uses BannerManager::getTmpPath()
	 * @uses MeCmsAppController::upload()
	 */
	public function admin_upload() {
		//Checks if the target directory is writable
		if(!is_writable($target = BannerManager::getTmpPath())) {
			$this->Session->flash(__d('me_cms', 'The directory %s is not readable or writable', $target), 'error');
			$this->redirect('/admin');
		}
		
		//Uploads the file
		if($this->request->is('post') &&!empty($this->request->params['form']['file']))
			$this->upload($this->request->params['form']['file'], $target);
		
		$this->set('title_for_layout', __d('me_cms', 'Upload banners'));
	}
	
	/**
	 * Open a banner target (link)
	 * @param string $id Banner ID
	 * @throws NotFoundException
	 */
	public function open($id = NULL) {
		$this->Banner->id = $id;
		if(!$this->Banner->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		//Gets the banner target
		$target = $this->Banner->field('target');
		
		//Checks for target
		if(empty($target))
			throw new NotFoundException(__d('me_cms', 'Invalid target'));
		
		//Increases the click count
		$this->Banner->updateAll(array('click_count' => 'click_count+1'), array('Banner.id' => $id));
		
		//Redirects
		$this->redirect($target);
	}
}