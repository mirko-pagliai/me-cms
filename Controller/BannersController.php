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
	 * @uses MeAuthComponenet::isAdmin()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can access this controller
		return $this->Auth->isAdmin();
	}
	
	/**
	 * List banners
	 */
	public function admin_index() {
		$this->paginate = array(
			'contain'	=> 'Position.name',
			'fields'	=> array('id', 'filename', 'target', 'description', 'active'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'banners'			=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Banners')
		));
	}

	/**
	 * Upload banner
	 * @throws InternalErrorException
	 */
	public function admin_upload() {
		//Gets the target directory
		$target = BannerManager::getTmpPath();
		
		//Checks if the target directory is writable
		if(!is_writable($target))
			throw new InternalErrorException(__d('me_cms', 'The directory %s is not readable or writable', $target));
		
		$error = FALSE;
		
		if($this->request->is('post')) {
			if(!empty($this->request->params['form']['file'])) {
				$file = $this->request->params['form']['file'];
				
				//Checks if the file was successfully uploaded
				if($file['error'] == UPLOAD_ERR_OK and is_uploaded_file($file['tmp_name'])) {
					//Updated the target, adding the file name
					if(!file_exists($target.DS.$file['name']))
						$target = $target.DS.$file['name'];
					//If the file already exists, adds the name of the temporary file to the file name
					else
						$target = $target.DS.pathinfo($file['name'], PATHINFO_FILENAME).'_'.basename($file['tmp_name']).'.'.pathinfo($file['name'], PATHINFO_EXTENSION);
					
					//Checks if the file was successfully moved to the target directory
					if(!move_uploaded_file($file['tmp_name'], $file['target'] = $target))
						$error = __d('me_cms', 'The file was not successfully moved to the target directory');
				}
				else
					$error = __d('me_cms', 'The file was not successfully uploaded');
				
				$this->set(compact('file'));				
			}
			else
				$error = __d('me_cms', 'An error occurred');
			
			$this->set(compact('error'));		
			
			//Renders
			$this->render('Elements/backend/uploader/response', FALSE);
		}
	}
	
	/**
	 * Add banner
	 * @uses BannerManager::getTmp()
	 * @uses BannerManager::getTmpPath()
	 */
	public function admin_add() {
		//Gets the positions and checks
		$positions = $this->Banner->Position->find('list');
		if(empty($positions)) {
			$this->Session->flash(__d('me_cms', 'Before you can add a banner, you have to create at least a banner position'), 'error');
			$this->redirect(array('controller' => 'banners_positions', 'action' => 'index'));
		}
		
		//Gets the temporary files and checks
		$tmpFiles = BannerManager::getTmp();
		if(empty($tmpFiles)) {
			$this->Session->flash(__d('me_cms', 'Before you can add a banner, you have to upload a banner'), 'error');
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
			
			//Sets the filename e the full path for the temporary file
			$tmpFile = array(
				'filename'	=> $tmpFiles[$this->request->query['file']],
				'path'		=> BannerManager::getTmpPath().DS.$tmpFiles[$this->request->query['file']]
			);
			
			$this->set(compact('tmpFile'));
		}
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Add banner')), compact('positions', 'tmpFiles')));
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
			$this->request->data = $this->Banner->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'position_id', 'filename', 'target', 'description', 'active')
			));

		$this->set(array(
			'positions'			=> $this->Banner->Position->find('list'),
			'title_for_layout'	=> __d('me_cms', 'Edit banner')
		));
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
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		//Increases the click count
		$this->Banner->updateAll(array('click_count' => 'click_count+1'), array('Banner.id' => $id));
		
		//Redirects
		$this->redirect($target);
	}
}