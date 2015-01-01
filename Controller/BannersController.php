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
			$this->Session->flash(__d('me_cms', 'There are no files in the temporary directory %s', BannerManager::getTmpPath()), 'error');
			$this->redirect(array('action' => 'index'));
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