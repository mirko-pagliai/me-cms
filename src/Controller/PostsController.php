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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license	http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\Time;
use MeCms\Controller\AppController;
use MeTools\Cache\Cache;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController {
	/**
     * Lists posts
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
    public function index() {
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();
		
		//Sets the cache name
		$cache = sprintf('index_limit_%s_page_%s', $this->paginate['limit'], $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->Posts->cache));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
			$posts = $this->paginate(
				$this->Posts->find('active')
					->contain([
						'Categories'	=> ['fields' => ['title', 'slug']],
						'Tags'			=> function($q) {
							return $q->order([sprintf('%s.post_count', $this->Posts->Tags->alias()) => 'DESC']);
						},
						'Users'			=> ['fields' => ['first_name', 'last_name']]
					])
					->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
					->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
			)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->Posts->cache);
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
		
        $this->set(compact('posts'));
    }
	
	/**
	 * Lists posts by a date
	 * @param int $year Year
	 * @param int $month Month
	 * @param int $day Day
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
	public function index_by_date($year, $month, $day) {
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();
		
		//Sets the cache name
		$cache = sprintf('index_date_%s_limit_%s_page_%s', md5(serialize([$year, $month, $day])), $this->paginate['limit'], $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->Posts->cache));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {		
			$posts = $this->paginate(
				$this->Posts->find('active')
					->contain([
						'Categories'	=> ['fields' => ['title', 'slug']],
						'Tags',
						'Users'			=> ['fields' => ['first_name', 'last_name']]
					])
					->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
					->where([
						sprintf('%s.created >=', $this->Posts->alias()) => (new Time())->setDate($year, $month, $day)->setTime(0, 0, 0)->i18nFormat(FORMAT_FOR_MYSQL),
						sprintf('%s.created <=', $this->Posts->alias()) => (new Time())->setDate($year, $month, $day)->setTime(23, 59, 59)->i18nFormat(FORMAT_FOR_MYSQL)
					])
					->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
			)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->Posts->cache);
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
		
        $this->set(compact('posts'));
		
		$this->render('Posts/index');
	}
	
	/**
	 * This allows backward compatibility for URLs like:
	 * <pre>/posts/page:3</pre>
	 * <pre>/posts/page:3/sort:Post.created/direction:desc</pre>
	 * These URLs will become:
	 * <pre>/posts?page=3</pre>
	 * @param int $page Page number
	 */
	public function index_compatibility($page) {
		return $this->redirect(['_name' => 'posts', '?' => ['page' => $page]], 301);
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
			->cache('rss', $this->Posts->cache));
		
		$this->viewBuilder()->layout('MeCms.frontend');
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
					list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->Posts->cache));

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
						Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->Posts->cache);
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
     * @throws RecordNotFoundException
	 * @uses MeCms\Model\Table\PostsTable::getRelated()
	 */
    public function view($slug = NULL) {
		$post = $this->Posts->find()
			->contain([
				'Categories'	=> ['fields' => ['title', 'slug']],
				'Tags',
				'Users'			=> ['fields' => ['first_name', 'last_name']]
			])
			->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created'])
			->where([sprintf('%s.slug', $this->Posts->alias()) => $slug])
			->cache(sprintf('view_%s', md5($slug)), $this->Posts->cache)
			->firstOrFail();
		
        //Checks created datetime and status. Logged users can view future posts and drafts
        if(!$this->Auth->user() && ($post->active || $post->created->isFuture()))
            throw new RecordNotFoundException(__d('me_cms', 'Record not found'));
        
        $this->set(compact('post'));
        
		//Gets related posts
		if(config('post.related.limit'))
			$this->set('related', $this->Posts->getRelated($post, config('post.related.limit'), config('post.related.images')));
	}
}