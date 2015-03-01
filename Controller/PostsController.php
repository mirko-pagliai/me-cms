<?php
/**
 * PostsController
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
 * Posts Controller
 */
class PostsController extends MeCmsAppController {
	/**
	 * Components
	 * @var array
	 */
	public $components = array('RequestHandler');
	
	/**
	 * Check if the provided user is authorized for the request.
	 * @param array $user The user to check the authorization of. If empty the user in the session will be used.
	 * @return bool TRUE if $user is authorized, otherwise FALSE
	 * @uses MeAuthComponent::isManager()
	 * @uses MeAuthComponent::user()
	 * @uses MeToolsAppController::isAction()
	 * @uses Post::isOwnedBy()
	 */
	public function isAuthorized($user = NULL) {
		//Only admins and managers can edit all posts
		//Users can edit only their own posts
		if($this->isAction('admin_edit') && !$this->Auth->isManager()) {
			$id = (int) $this->request->params['pass'][0];
			return $this->Post->isOwnedBy($id, $this->Auth->user('id'));
		}
		
		//Only admins and managers can delete posts
		if($this->isAction('admin_delete'))
			return $this->Auth->isManager();
		
		return TRUE;
	}
	
	/**
	 * Called after the controller action is run, but before the view is rendered. 
	 * It's used to perform logic or set view variables that are required on every request.
	 * @uses ConfigComponent::kcfinder()
	 * @uses MeToolsAppController::isAction()
	 */
	public function beforeRender() {
		parent::beforeRender();
		
		//Configures KCFinder for some actions
		if($this->isAction(array('admin_add', 'admin_edit')))
			$this->Config->kcfinder();
	}

	/**
	 * Add post
	 * @uses MeAuthComponent::isManager()
	 */
	public function admin_add() {
		//Gets categories
		$categories = $this->Post->Category->generateTreeList();
		
		//Checks for categories
		if(empty($categories)) {
			$this->Session->flash(__d('me_cms', 'Before you can add a post, you have to create at least a category'), 'alert');
			$this->redirect(array('controller' => 'posts_categories', 'action' => 'index'));
		}
		
		//Gets users
		$users = $this->Post->User->find('list', array('fields' => array('id', 'full_name')));
		
		if($this->request->is('post')) {
			//Only admins and managers can add posts on behalf of other users
			if(!$this->Auth->isManager())
				$this->request->data['Post']['user_id'] = $this->Auth->user('id');
			
			$this->Post->create();
			if($this->Post->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The post has been created'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The post could not be created. Please, try again'), 'error');
		}

		$this->set(am(array('title_for_layout' => __d('me_cms', 'Add post')), compact('categories', 'users')));
	}

	/**
	 * Delete post
	 * @param string $id Post id
	 * @throws NotFoundException
	 */
	public function admin_delete($id = NULL) {
		$this->Post->id = $id;
		if(!$this->Post->exists())
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
			
		$this->request->onlyAllow('post', 'delete');
		
		if($this->Post->delete())
			$this->Session->flash(__d('me_cms', 'The post has been deleted'));
		else
			$this->Session->flash(__d('me_cms', 'The post was not deleted'), 'error');
			
		$this->redirect(array('action' => 'index'));
	}

	/**
	 * Edit post
	 * @param string $id Post id
	 * @throws NotFoundException
	 * @uses MeAuthComponent::isManager()
	 */
	public function admin_edit($id = NULL) {
		if(!$this->Post->exists($id))
			throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
		//Gets categories
		$categories = $this->Post->Category->generateTreeList();
		
		//Gets users
		$users = $this->Post->User->find('list', array('fields' => array('id', 'full_name')));
					
		if($this->request->is('post') || $this->request->is('put')) {
			//Only admins and managers can edit posts on behalf of other users
			if(!$this->Auth->isManager())
				$this->request->data['Post']['user_id'] = $this->Auth->user('id');
			
			if($this->Post->save($this->request->data)) {
				$this->Session->flash(__d('me_cms', 'The post has been edited'));
				$this->redirect(array('action' => 'index'));
			}
			else
				$this->Session->flash(__d('me_cms', 'The post could not be edited. Please, try again'), 'error');
		} 
		else
			$this->request->data = $this->Post->findById($id, array(
				'id', 'category_id', 'user_id', 'title', 'subtitle', 'slug', 'text', 'created', 'priority', 'active'
			));

		$this->set(am(array('title_for_layout' => __d('me_cms', 'Edit post')), compact('categories', 'users')));
	}
	
	/**
	 * List posts
	 * @uses MeCmsAppModel::conditionsFromFilter()
	 */
	public function admin_index() {
		//Sets conditions from the filter form
		$conditions = empty($this->request->query) ? array() : $this->Post->conditionsFromFilter($this->request->query);
		
		$this->paginate = am(array(
			'contain'	=> array('Category.title', 'User.first_name', 'User.last_name'),
			'fields'	=> array('id', 'title', 'slug', 'priority', 'active', 'created'),
			'limit'		=> $this->config['backend']['records'],
			'order'		=> array('Post.created' => 'DESC')
		), compact('conditions'));
				
		$this->set(array(
			'categories'		=> $this->Post->Category->find('list', array('fields' => array('id', 'title'))),
			'posts'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Posts'),
			'users'				=> $this->Post->User->find('list')
		));
	}

	/**
	 * List posts
	 * @param string $category Category slug, optional
	 */
	public function index($category = NULL) {
		//Sets the initial cache name
		$cache = 'index';
		//Sets the initial conditions query
		$conditions = array();
		
		//Checks if has been specified a category
		if(!empty($category) || !empty($this->request->query['category'])) {
			//The category can also be passed as query
			$category = empty($category) ? $this->request->query['category'] : $category;
			
			//Adds the category to the conditions, if it has been specified
			$conditions['Category.slug'] = $category;
			
			//Updates the cache name with the category name
			$cache = sprintf('%s_%s', $cache, $category);
		}
		
		//Updates the cache name with the number of the page
		$cache = sprintf('%s_page_%s', $cache, empty($this->request->named['page']) ? '1' : $this->request->named['page']);
						
		//Tries to get data from the cache
		$posts = Cache::read($cache, 'posts');
		$paging = Cache::read(sprintf('%s_paging', $cache), 'posts');
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
			$this->paginate = am(array(
				'contain'	=> array('Category.title', 'Category.slug', 'User.first_name', 'User.last_name'),
				'fields'	=> array('title', 'subtitle', 'slug', 'text', 'created'),
				'findType'	=> 'active',
				'limit'		=> $this->config['frontend']['records'],
				'order'		=> array('Post.created' => 'DESC')
			), compact('conditions'));
			
            Cache::write($cache, $posts = $this->paginate(), 'posts');
			Cache::write(sprintf('%s_paging', $cache), $this->request->params['paging'], 'posts');
		}
		//Else, sets the paging params
		else
			$this->request->params['paging'] = $paging;
		
		//Sets the category title as the title of the layout, if it's available
		if(!empty($category) && !empty($posts[0]['Category']['title']))
			$title_for_layout = $posts[0]['Category']['title'];
		else
			$title_for_layout = __d('me_cms', 'Posts');
		
		$this->set(compact('posts', 'title_for_layout'));
	}
	
	/**
	 * Lists posts as RSS.
	 * @return array Posts
	 * @throws ForbiddenException
	 */
	public function rss() {
		//This method works only for RSS
		if(!$this->RequestHandler->isRss())
            throw new ForbiddenException();
		
		//Tries to get data from the cache
		$posts = Cache::read($cache = 'rss', 'posts');

		//If the data are not available from the cache
		if(empty($posts)) {
			$posts = $this->Post->find('active', array(
				'fields'	=> array('title', 'slug', 'text', 'created'),
				'limit'		=> 20
			));

			Cache::write($cache, $posts, 'posts');
		}

		return $this->set(compact('posts'));
	}
	
	/**
	 * Search posts.
	 * @uses MeCmsAppController::_checkLastSearch()
	 */
	public function search() {
		$pattern = empty($this->request->query['p']) ? FALSE : trim($this->request->query['p']);
		
		if(!empty($pattern)) {
			//Checks if the pattern is at least 4 characters long
			if(strlen($pattern) >= 4) {
				//Checks if the latest search has been executed out of the minimum interval
				if($this->_checkLastSearch()) {
					$this->paginate = array(
						'conditions'	=> array('OR' => array(
							'title LIKE'	=> sprintf('%%%s%%', $pattern),
							'subtitle LIKE' => sprintf('%%%s%%', $pattern),
							'text LIKE'		=> sprintf('%%%s%%', $pattern)
						)),
						'fields'		=> array('title', 'slug', 'text', 'created'),
						'findType'		=> 'active',
						'limit'			=> 10
					);

					try {
						$posts = $this->paginate();
						$count = $this->request->params['paging']['Post']['count'];
					}
					catch(NotFoundException $e) {}

					$this->set(compact('count', 'posts'));
				}
				else
					$this->Session->flash(__d('me_cms', 'You have to wait %d seconds to perform a new search', $this->config['security']['search_interval']), 'alert');
			}
			else
				$this->Session->flash(__d('me_cms', 'You have to search at least a word of %d characters', 4), 'alert');
		}
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Search posts')), compact('pattern')));
	}
	
	/**
	 * View post
	 * @param string $slug Slug
	 * @throws NotFoundException
	 */
	public function view($slug = NULL) {
		//Tries to get data from the cache
		$post = Cache::read($cache = sprintf('view_%s', $slug), 'posts');
		
		//If the data are not available from the cache
		if(empty($post)) {
			$post = $this->Post->find('active', array(
				'conditions'	=> array('Post.slug' => $slug),
				'contain'		=> array('Category.title', 'Category.slug', 'User.first_name', 'User.last_name'),
				'fields'		=> array('title', 'subtitle', 'slug', 'text', 'created'),
				'limit'			=> 1
			));

			if(empty($post))
				throw new NotFoundException(__d('me_cms', 'Invalid object'));
		
            Cache::write($cache, $post, 'posts');			
		}
		
		$image_src = empty($post['Post']['preview']) ? NULL : $post['Post']['preview'];
		
		$this->set(am(array('title_for_layout'	=> $post['Post']['title']), compact('image_src', 'post')));
	}
	
	/**
	 * Gets the latest posts for widget.
	 * This method works only with `requestAction()`.
	 * @param int $limit Number of latest posts
	 * @return array List of latest posts
	 * @throws ForbiddenException
	 * @uses MeToolsAppController::isRequestAction()
	 */
	public function widget_latest($limit = 10) {
		//This method works only with "requestAction()"
		if(!$this->isRequestAction())
            throw new ForbiddenException();
		
		//Tries to get data from the cache
		$posts = Cache::read($cache = sprintf('widget_latest_%d', $limit), 'posts');
		
		//If the data are not available from the cache
        if(empty($posts)) {
            $posts = $this->Post->find('active', am(array('fields' => array('slug', 'title')), compact('limit')));
			
            Cache::write($cache, $posts, 'posts');
        }
		
		return $posts;
	}
}