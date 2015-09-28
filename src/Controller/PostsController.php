<?php
/**
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
 */
namespace MeCms\Controller;

use Cake\Cache\Cache;
use MeCms\Controller\AppController;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController {
	/**
     * Lists posts
	 * @param string $category Category slug (optional)
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
    public function index($category = NULL) {
		//The category can be passed as query string, from a widget
		if($this->request->query('q'))
			$this->redirect([$this->request->query('q')]);
		
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();
		
		//Sets the initial cache name
		$cache = 'index';
		
		//Checks if has been specified a category
		if(!empty($category)) {
			//Adds the category to the conditions, if it has been specified
			$conditions['Categories.slug'] = $category;
			
			//Updates the cache name, adding the category name
			$cache = sprintf('%s_%s', $cache, md5($category));
		}
		
		//Updates the cache name with the query limit and the number of the page
		$cache = sprintf('%s_limit_%s', $cache, $this->paginate['limit']);
		$cache = sprintf('%s_page_%s', $cache, $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], 'posts'));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
			$posts = $this->paginate(
				$this->Posts->find('active')
					->contain([
						'Categories'	=> ['fields' => ['title', 'slug']],
						'Users'			=> ['fields' => ['first_name', 'last_name']]
					])
					->select(['title', 'subtitle', 'slug', 'text', 'created'])
					->where(empty($conditions) ? [] : $conditions)
					->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
			)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], 'posts');
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
				
		//Sets the category title as title, if has been specified a category
		if(!empty($category) && !empty($posts[0]->category->title))
			$this->set('title', $posts[0]->category->title);
		
        $this->set(compact('posts'));
    }
	
	/**
	 * Lists posts as RSS
	 * @throws \Cake\Network\Exception\ForbiddenException
	 * @uses Cake\Controller\Component\RequestHandlerComponent:isRss()
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
	public function rss() {
		//This method works only for RSS
		if(!$this->RequestHandler->isRss())
            throw new \Cake\Network\Exception\ForbiddenException();
		
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();
		
		$this->set('posts', $this->Posts->find('active')
			->select(['title', 'slug', 'text', 'created'])
			->limit(config('frontend.records_for_rss'))
			->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
			->cache('rss', 'posts'));
	}
	
	/**
	 * Search posts
	 * @uses MeCms\Controller\Component\SecurityComponent::checkLastSearch()
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
	public function search() {
		if($pattern = $this->request->query('p')) {
			//Checks if the pattern is at least 4 characters long
			if(strlen($pattern) >= 4) {
				if($this->Security->checkLastSearch($pattern)) {
					$this->paginate['limit'] = config('frontend.records_for_searches');
					
					//Checks if the cache is valid
					$this->Posts->checkIfCacheIsValid();
					
					//Sets the initial cache name
					$cache = sprintf('search_%s', md5($pattern));

					//Updates the cache name with the query limit and the number of the page
					$cache = sprintf('%s_limit_%s', $cache, $this->paginate['limit']);
					$cache = sprintf('%s_page_%s', $cache, $this->request->query('page') ? $this->request->query('page') : 1);

					//Tries to get data from the cache
					list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], 'posts'));

					//If the data are not available from the cache
					if(empty($posts) || empty($paging)) {
						$posts = $this->paginate(
							$this->Posts->find('active')
								->select(['title', 'slug', 'text', 'created'])
								->where(['OR' => [
									'title LIKE'	=> sprintf('%%%s%%', $pattern),
									'subtitle LIKE' => sprintf('%%%s%%', $pattern),
									'text LIKE'		=> sprintf('%%%s%%', $pattern)
								]])
								->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
						)->toArray();

						//Writes on cache
						Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], 'posts');
					}
					//Else, sets the paging parameter
					else
						$this->request->params['paging'] = $paging;

					$this->set(compact('posts'));
				}
				else
					$this->Flash->alert(__d('me_cms', 'You have to wait {0} seconds to perform a new search', config('security.search_interval')));
			}
			else
				$this->Flash->alert(__d('me_cms', 'You have to search at least a word of {0} characters', 4));
		}
	}
	
	/**
     * Views post
	 * @param string $slug Post slug
     * @throws \Cake\Network\Exception\NotFoundException
	 */
    public function view($slug = NULL) {
		$this->set('post', $this->Posts->find('active')
			->contain([
				'Categories'	=> ['fields' => ['title', 'slug']],
				'Users'			=> ['fields' => ['first_name', 'last_name']]
			])
			->select(['title', 'subtitle', 'slug', 'text', 'created'])
			->where([sprintf('%s.slug', $this->Posts->alias()) => $slug])
			->cache(sprintf('view_%s', md5($slug)), 'posts')
			->first());
    }
}