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
}