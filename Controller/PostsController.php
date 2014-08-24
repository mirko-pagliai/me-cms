<?php
App::uses('MeCmsBackendAppController', 'MeCmsBackend.Controller');

/**
 * PostsController
 *
 * This file is part of MeCms Backend
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\Controller
 */

/**
 * Posts Controller
 */
class PostsController extends MeCmsBackendAppController {	
	/**
	 * List posts
	 */
	public function admin_index() {
		$this->paginate = array(
			'contain'	=> array('Category.title', 'User.username'),
			'fields'	=> array('id', 'title', 'priority', 'active', 'created'),
			'limit'		=> $this->config['site']['records_for_page'],
			'order'		=> array('created' => 'desc')
		);
		
		$this->set(array(
			'posts'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms_backend', 'Posts')
		));
	}

	/**
	 * Add post
	 */
	public function admin_add() {
		//Gets categories
		$categories = $this->Post->Category->generateTreeList();
		
		//Checks for categories
		if(empty($categories)) {
			$this->Session->flash(__d('me_cms_backend', 'Before you can add a post, you have to create at least one category'), 'error');
			$this->redirect(array('controller' => 'posts_categories', 'action' => 'index'));
		}
		
		if($this->request->is('post')) {
			$this->Post->create();
			if($this->Post->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The post has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The post could not be created. Please, try again'), 'error');
		}

		$this->set(array(
			'categories'		=> $categories,
			'title_for_layout'	=> __d('me_cms_backend', 'Add post'),
			'users'				=> $this->Post->User->find('list')
		));
	}

	/**
	 * Edit post
	 * @param string $id Post id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Post->exists($id))
			throw new NotFoundException(__d('me_cms_backend', 'Invalid post'));
					
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->Post->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The post has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The post could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->Post->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'category_id', 'user_id', 'title', 'slug', 'text', 'created', 'priority', 'active')
			));

		$this->set(array(
			'categories'		=> $this->Post->Category->generateTreeList(),
			'title_for_layout'	=> __d('me_cms_backend', 'Edit post'),
			'users'				=> $this->Post->User->find('list')
		));
	}

	/**
	 * Delete post
	 * @param string $id Post id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Post->id = $id;
		if(!$this->Post->exists())
			throw new NotFoundException(__d('me_cms_backend', 'Invalid post'));
			
		$this->request->onlyAllow('post', 'delete');
		
		if($this->Post->delete())
			$this->Session->flash(__d('me_cms_backend', 'The post has been deleted'));
		else
			$this->Session->flash(__d('me_cms_backend', 'The post was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}
}