<?php
/**
 * PostsCategoriesController
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

App::uses('MeCmsBackendAppController', 'MeCmsBackend.Controller');
/**
 * PostsCategories Controller
 */
class PostsCategoriesController extends MeCmsBackendAppController {	
	/**
	 * List posts categories
	 */
	public function admin_index() {
		//Gets the categories
		$categories = $this->PostsCategory->find('all', array(
			'contain'	=> array('Parent.title'),
			'fields'	=> array('id', 'slug', 'post_count')
		));
		
		//Gets the tree list
		$treeList = $this->PostsCategory->generateTreeList();
		
		//Changes the category titles, replacing them with the titles of the tree list
		array_walk($categories, function(&$v, $k, $treeList) {
			$v['PostsCategory']['title'] = $treeList[$v['PostsCategory']['id']];
		}, $treeList);
		
		$this->set(array(
			'postsCategories'	=> $categories,
			'title_for_layout'	=> __d('me_cms_backend', 'Posts categories'))
		);
	}

	/**
	 * Add posts category
	 */
	public function admin_add() {		
		if($this->request->is('post')) {
			$this->PostsCategory->create();
			if($this->PostsCategory->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The posts category has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The posts category could not be created. Please, try again'), 'error');
		}

		$this->set(array(
			'parents'			=> $this->PostsCategory->generateTreeList(),
			'title_for_layout'	=> __d('me_cms_backend', 'Add posts category')
		));
	}

	/**
	 * Edit posts category
	 * @param string $id Posts category id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->PostsCategory->exists($id))
			throw new NotFoundException(__d('me_cms_backend', 'Invalid posts category'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->PostsCategory->save($this->request->data)) {
				$this->Session->flash(__d('me_cms_backend', 'The posts category has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms_backend', 'The posts category could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->PostsCategory->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'parent_id', 'title', 'slug', 'description')
			));

		$this->set(array(
			'parents'			=> $this->PostsCategory->generateTreeList(),
			'title_for_layout'	=> __d('me_cms_backend', 'Edit posts category')
		));
	}

	/**
	 * Delete posts category
	 * @param string $id Posts category id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->PostsCategory->id = $id;
		if(!$this->PostsCategory->exists())
			throw new NotFoundException(__d('me_cms_backend', 'Invalid posts category'));
			
		$this->request->onlyAllow('post', 'delete');
		
		//Before deleting, it checks if the category has some posts
		if(!$this->PostsCategory->field('post_count')) {
			if($this->PostsCategory->delete())
				$this->Session->flash(__d('me_cms_backend', 'The posts category has been deleted'));
			else
				$this->Session->flash(__d('me_cms_backend', 'The posts category was not deleted'), 'error');
		}
		else
			$this->Session->flash(__d('me_cms_backend', 'Before you delete this category, you have to delete its posts or assign them to another category'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}
}