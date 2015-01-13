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
	 * @uses isAction()
	 * @uses MeAuthComponenet::isManager()
	 * @uses MeAuthComponenet::user()
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
	 * List posts
	 */
	public function admin_index() {
		$this->paginate = array(
			'contain'	=> array('Category.title', 'User.first_name', 'User.last_name'),
			'fields'	=> array('id', 'title', 'slug', 'priority', 'active', 'created'),
			'limit'		=> $this->config['records_for_page']
		);
		
		$this->set(array(
			'posts'				=> $this->paginate(),
			'title_for_layout'	=> __d('me_cms', 'Posts')
		));
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
			$this->Session->flash(__d('me_cms', 'Before you can add a post, you have to create at least a category'), 'error');
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
			$this->request->data = $this->Post->find('first', array(
				'conditions'	=> array('id' => $id),
				'fields'		=> array('id', 'category_id', 'user_id', 'title', 'subtitle', 'slug', 'text', 'created', 'priority', 'active')
			));

		$this->set(am(array('title_for_layout' => __d('me_cms', 'Edit post')), compact('categories', 'users')));
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
	 * Gets the latest posts.
	 * This method works only with `requestAction()`.
	 * @param int $limit Number of latest posts
	 * @return array Latest posts
	 * @throws ForbiddenException
	 */
	public function request_latest($limit = 5) {
		//This method works only with "requestAction()"
		if(!$this->isRequestAction())
            throw new ForbiddenException();
		
		//Tries to get data from the cache
		$posts = Cache::read($cache = sprintf('posts_request_latest_%d', $limit), 'posts');
		
		//If the data are not available from the cache
        if(empty($posts)) {
            $posts = $this->Post->find('active', am(array(
				'contain'	=> array('Category.title', 'Category.slug', 'User.first_name', 'User.last_name'),
				'fields'	=> array('title', 'subtitle', 'slug', 'text', 'created')
			), compact('limit')));
			
            Cache::write($cache, $posts, 'posts');
        }
		
		return $posts;
	}
	
	/**
	 * Gets the latest posts as list.
	 * This method works only with `requestAction()`.
	 * @param int $limit Number of latest posts
	 * @return array List of latest posts
	 * @throws ForbiddenException
	 * @uses isRequestAction()
	 */
	public function request_latest_list($limit = 10) {
		//This method works only with "requestAction()"
		if(!$this->isRequestAction())
            throw new ForbiddenException();
		
		//Tries to get data from the cache
		$posts = Cache::read($cache = sprintf('posts_request_latest_%d_list', $limit), 'posts');
		
		//If the data are not available from the cache
        if(empty($posts)) {
            $posts = $this->Post->find('active', am(array('fields' => array('slug', 'title')), compact('limit')));
			
            Cache::write($cache, $posts, 'posts');
        }
		
		return $posts;
	}

	/**
	 * List posts
	 * @param string $category Category slug, optional
	 * @return array List of latest posts (only when requested as rss)
	 */
	public function index($category = NULL) {
		//If the posts were requested as rss
		if($this->RequestHandler->isRss()) {
			//Tries to get data from the cache
			$posts = Cache::read($cache = 'posts_rss', 'posts');

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

		//Sets the initial cache name
		$cache = 'posts_index';
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
			$this->paginate = array(
				'conditions'	=> $conditions,
				'contain'		=> array('Category.title', 'Category.slug', 'User.first_name', 'User.last_name'),
				'fields'		=> array('title', 'subtitle', 'slug', 'text', 'created'),
				'findType'		=> 'active',
				'limit'			=> $this->config['records_for_page']
			);
			
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
	 * View post
	 * @param string $slug Post slug
	 * @throws NotFoundException
	 */
	public function view($slug = NULL) {
		//Tries to get data from the cache
		$post = Cache::read($cache = sprintf('posts_view_%s', $slug), 'posts');
		
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
			
			//Gets the first image for the "image_src" tag
			preg_match('#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im', $post['Post']['text'], $matches);
			if(!empty($matches[2]))
				$post['Post']['preview'] = Router::url($matches[2], TRUE);
		
            Cache::write($cache, $post, 'posts');			
		}
		
		$this->set(am(array(
			'image_src'			=> $post['Post']['preview'],
			'title_for_layout'	=> $post['Post']['title']
		), compact('post')));
	}
	
	/**
	 * Search post
	 */
	public function search() {
		$pattern = empty($this->request->query['p']) ? FALSE : trim($this->request->query['p']);
		
		if(!empty($pattern)) {
			//Checks if the pattern is at least 4 characters long
			if(strlen($pattern) >= 4) {
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
				$this->Session->flash(__d('me_cms', 'You have to search at least a word of %d characters', 4), 'error');
		}
		
		$this->set(am(array('title_for_layout' => __d('me_cms', 'Search posts')), compact('pattern')));
	}
}