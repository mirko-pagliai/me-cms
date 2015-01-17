<?php
/**
 * PostsCategoriesController
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

/**
 * PostsCategories Controller
 */
class PostsCategoriesController extends MeCmsAppController {
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponenet::isManager()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can access this controller
		return $this->Auth->isManager();
	}

	/**
	 * Add category
	 */
	public function admin_add() {		
		if($this->request->is('post')) {
			$this->PostsCategory->create();
			if($this->PostsCategory->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The posts category has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The posts category could not be created. Please, try again'), 'error');
		}

		$this->set(array(
			'parents'			=> $this->PostsCategory->generateTreeList(),
			'title_for_layout'	=> __d('me_cms', 'Add posts category')
		));
	}

	/**
	 * Delete category
	 * @param string $id Category id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->PostsCategory->id = $id;
		if(!$this->PostsCategory->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		$this->request->onlyAllow('post', 'delete');
		
		//Before deleting, it checks if the category has some posts
		if(!$this->PostsCategory->field('post_count')) {
			if($this->PostsCategory->delete())
				$this->Session->flash(__d('me_cms', 'The posts category has been deleted'));
			else
				$this->Session->flash(__d('me_cms', 'The posts category was not deleted'), 'error');
		}
		else
			$this->Session->flash(__d('me_cms', 'Before you delete this category, you have to delete its posts or assign them to another category'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}

	/**
	 * Edit category
	 * @param string $id Category id
	 * @throws NotFoundException
	 */
	public function admin_edit($id = NULL) {
		if(!$this->PostsCategory->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		if($this->request->is('post') || $this->request->is('put')) {
			if($this->PostsCategory->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The posts category has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The posts category could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->PostsCategory->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'parent_id', 'title', 'slug', 'description')
			));

		$this->set(array(
			'parents'			=> $this->PostsCategory->generateTreeList(),
			'title_for_layout'	=> __d('me_cms', 'Edit posts category')
		));
	}
	
	/**
	 * List categories
	 */
	public function admin_index() {
		//Gets the categories
		$categories = $this->PostsCategory->find('all', array(
			'contain'	=> 'Parent.title',
			'fields'	=> array('id', 'slug', 'post_count')
		));
		
		//Changes the category titles, replacing them with the titles of the tree list
		array_walk($categories, function(&$v, $k, $treeList) {
			$v['PostsCategory']['title'] = $treeList[$v['PostsCategory']['id']];
		}, $this->PostsCategory->generateTreeList());
		
		$this->set(array(
			'categories'		=> $categories,
			'title_for_layout'	=> __d('me_cms', 'Posts categories'))
		);
	}
	
	/**
	 * List categories
	 */
	public function index() {
		//Tries to get data from the cache
		$categories = Cache::read($cache = 'posts_categories_index', 'posts');
		
		//If the data are not available from the cache
        if(empty($categories)) {
			$categories = $this->PostsCategory->find('active', array('fields' => array('title', 'slug')));
			
            Cache::write($cache, $categories, 'posts');
        }
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Posts categories')), compact('categories')));
	}
	
	/**
	 * Gets the categories list for widget.
	 * This method works only with `requestAction()`.
	 * @return array Categories list
	 * @throws ForbiddenException
	 * @uses isRequestAction()
	 */
	public function widget_list() {
		//This method works only with "requestAction()"
		if(!$this->isRequestAction())
            throw new ForbiddenException();
		
		//Tries to get data from the cache
		$categories = Cache::read($cache = 'posts_categories_widget_list', 'posts');
		
		//If the data are not available from the cache
        if(empty($categories)) {
			//Gets the categories
			$catsTmp = $this->PostsCategory->find('active', array('fields' => array('id', 'slug', 'post_count')));
			
			if(empty($catsTmp))
				return array();

			//Gets the tree list
			$treeList = $this->PostsCategory->generateTreeList();
			
			$categories = array();
			
			foreach($catsTmp as $category) {
				//Changes the category titles, replacing them with the titles of the tree list and adding the "post_count" value
				$category['PostsCategory']['title'] = sprintf('%s (%s)', $treeList[$category['PostsCategory']['id']], $category['PostsCategory']['post_count']);

				//The new array has the slug as key and the title as value
				$categories[$category['PostsCategory']['slug']] = $category['PostsCategory']['title'];
			}
			
            Cache::write($cache, $categories, 'posts');
        }
		
		return $categories;
	}
}