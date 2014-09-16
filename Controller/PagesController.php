<?php
/**
 * PagesController
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

/**
 * Pages Controller
 */
class PagesController extends MeCmsAppController {
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isAction()
	 * @uses MeAuthComponenet::isAdmin()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins can delete pages
		if($this->Auth->isAction('delete'))
			return $this->Auth->isAdmin();
		
		return parent::isAuthorized($user);
	}
	
	/**
	 * List pages
	 */
	public function admin_index() {
		$this->paginate = array(
			'fields'	=> array('id', 'title', 'slug', 'priority', 'active', 'created'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'pages'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Pages')
		));
	}

	/**
	 * Add page
	 */
	public function admin_add() {
		if($this->request->is('post')) {
			$this->Page->create();
			if($this->Page->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The page has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The page could not be created. Please, try again'), 'error');
		}

		$this->set('title_for_layout', __d('me_cms', 'Add page'));
	}

	/**
	 * Edit page
	 * @param string $id Page id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Page->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid page'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->Page->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The page has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The page could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->Page->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'title', 'slug', 'text', 'priority', 'active', 'created')
			));

		$this->set('title_for_layout', __d('me_cms', 'Edit page'));
	}

	/**
	 * Delete page
	 * @param string $id Page id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Page->id = $id;
		if(!$this->Page->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid page'));
			
		$this->request->onlyAllow('post', 'delete');
		
		if($this->Page->delete())
			$this->Session->flash(__d('me_cms', 'The page has been deleted'));
		else
			$this->Session->flash(__d('me_cms', 'The page was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}
	
	/**
	 * Gets the pages list, with the slug as key and the title as value.
	 * This method works only with `requestAction()`.
	 * @return array Pages list
	 * @throws ForbiddenException
	 */
	public function request_list() {
		//This method works only with "requestAction()"
		if(empty($this->request->params['requested']))
            throw new ForbiddenException();
		
		return $this->Page->find('active', array('fields' => array('title', 'slug')));
	}
	
	/**
	 * List pages
	 */
	public function index() {
		$pages = $this->Page->find('active', array('fields' => array('title', 'slug')));
		
		$this->set(array(
			'pages'				=> $pages,
			'title_for_layout'	=> __d('me_cms', 'Pages')
		));
	}
	
	/**
	 * View page
	 * @param string $slug Page slug
	 * @throws NotFoundException
	 */
	public function view($slug = NULL) {
		$page = $this->Page->find('active', array(
			'conditions'	=> array('slug' => $slug),
			'fields'		=> array('title', 'slug', 'text', 'created'),
			'limit'			=> 1
		));
		
		if(empty($page))
			throw new NotFoundException(__d('me_cms', 'Invalid page'));
		
		$this->set(array(
			'page'				=> $page,
			'title_for_layout'	=> $page['Page']['title']
		));
	}
}