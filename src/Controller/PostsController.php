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
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use Cake\Cache\Cache;
use Cake\I18n\Time;
use MeCms\Controller\AppController;

/**
 * Posts controller
 * @property \MeCms\Model\Table\PostsTable $Posts
 */
class PostsController extends AppController {
	/**
	 * Called before the controller action. 
	 * You can use this method to perform logic that needs to happen before each controller action.
	 * @param \Cake\Event\Event $event An Event instance
	 * @see http://api.cakephp.org/3.2/class-Cake.Controller.Controller.html#_beforeFilter
	 * @uses MeCms\Controller\AppController::beforeFilter()
	 */
	public function beforeFilter(\Cake\Event\Event $event) {
        parent::beforeFilter($event);
        
        $this->Auth->deny('preview');
    }
    
	/**
     * Lists posts
	 */
    public function index() {		
		//Sets the cache name
		$cache = sprintf('index_limit_%s_page_%s', $this->paginate['limit'], $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->Posts->cache));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
            $query = $this->Posts->find('active')
                ->contain([
                    'Categories' => ['fields' => ['title', 'slug']],
                    'Tags' => function($q) {
                        return $q->order([sprintf('%s.post_count', $this->Posts->Tags->alias()) => 'DESC']);
                    },
                    'Users' => ['fields' => ['first_name', 'last_name']],
                ])
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC']);
            
			$posts = $this->paginate($query)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->Posts->cache);
		}
		//Else, sets the paging parameter
		else {
			$this->request->params['paging'] = $paging;
        }
        
        $this->set(compact('posts'));
    }
    
    /**
	 * Internal method to list posts in a date interval
     * @param Time $start Start time
     * @param Time $end End time
     */
    protected function _index_by_date(Time $start, Time $end) {
        $page = $this->request->query('page') ? $this->request->query('page') : 1;
        
        //Sets the cache name
		$cache = sprintf('index_date_%s_limit_%s_page_%s', md5(serialize([$start, $end])), $this->paginate['limit'], $page);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->Posts->cache));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {        
            $query = $this->Posts->find('active')
                ->contain([
                    'Categories' => ['fields' => ['title', 'slug']],
                    'Tags',
                    'Users' => ['fields' => ['first_name', 'last_name']],
                ])
                ->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
                ->where([
                    sprintf('%s.created >=', $this->Posts->alias()) => $start,
                    sprintf('%s.created <', $this->Posts->alias()) => $end,
                ])
                ->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC']);
            
			$posts = $this->paginate($query)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->Posts->cache);
		}
		//Else, sets the paging parameter
		else {
			$this->request->params['paging'] = $paging;
        }
        
        $this->set(compact('posts'));
    }
    
    /**
	 * Lists posts by a day (year, month and day).
     * It uses the `index` template.
	 * @param int $year Year
	 * @param int $month Month
	 * @param int $day Day
     * @uses _index_by_date()
	 */
	public function index_by_day($year, $month, $day) {
        $start = (new Time())->setDate($year, $month, $day)->setTime(0, 0, 0);
        $end = (new Time($start))->addDay(1);
        
        $this->_index_by_date($start, $end);
		
		$this->render('index');
	}
    
    /**
	 * Lists posts by a month (year and month).
     * It uses the `index` template.
	 * @param int $year Year
	 * @param int $month Month
     * @uses _index_by_date()
     */
    public function index_by_month($year, $month) {
        //Data can be passed as query string, from a widget
		if($this->request->query('q')) {
            $exploded = explode('-', $this->request->query('q'));
			return $this->redirect([$exploded[1], $exploded[0]]);
        }
        
        $start = (new Time())->setDate($year, $month, 1)->setTime(0, 0, 0);
        $end = (new Time($start))->addMonth(1);
        
        $this->_index_by_date($start, $end);
		
		$this->render('index');
    }
    
    /**
	 * Lists posts by a year.
     * It uses the `index` template.
	 * @param int $year Year
     * @uses _index_by_date()
     */
    public function index_by_year($year) {
        $start = (new Time())->setDate($year, 1, 1)->setTime(0, 0, 0);
        $end = (new Time($start))->addYear(1);
        
        $this->_index_by_date($start, $end);
        
		$this->render('index');
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
	 */
	public function rss() {
		//This method works only for RSS
		if(!$this->RequestHandler->isRss()) {
            throw new \Cake\Network\Exception\ForbiddenException();
        }
        
        $posts = $this->Posts->find('active')
			->select(['title', 'slug', 'text', 'created'])
			->limit(config('frontend.records_for_rss'))
			->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
			->cache('rss', $this->Posts->cache);
        
        $this->set(compact('posts'));
		
		$this->viewBuilder()->layout('MeCms.frontend');
	}
	
	/**
	 * Searches posts
	 * @uses MeCms\Controller\AppController::_checkLastSearch()
	 */
	public function search() {
        $pattern = $this->request->query('p');
        
		if($pattern) {
			//Checks if the pattern is at least 4 characters long
			if(strlen($pattern) >= 4) {
				if($this->_checkLastSearch($pattern)) {
					$this->paginate['limit'] = config('frontend.records_for_searches');
					
					//Sets the initial cache name
					$cache = sprintf('search_%s', md5($pattern));

					//Updates the cache name with the query limit and the number of the page
					$cache = sprintf('%s_limit_%s', $cache, $this->paginate['limit']);
					$cache = sprintf('%s_page_%s', $cache, $this->request->query('page') ? $this->request->query('page') : 1);

					//Tries to get data from the cache
					list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->Posts->cache));

					//If the data are not available from the cache
					if(empty($posts) || empty($paging)) {
                        $query = $this->Posts->find('active')
                            ->select(['title', 'slug', 'text', 'created'])
                            ->where(['OR' => [
                                'title LIKE' => sprintf('%%%s%%', $pattern),
                                'subtitle LIKE' => sprintf('%%%s%%', $pattern),
                                'text LIKE' => sprintf('%%%s%%', $pattern),
                            ]])
                            ->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC']);
                        
						$posts = $this->paginate($query)->toArray();

						//Writes on cache
						Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->Posts->cache);
					}
					//Else, sets the paging parameter
					else {
						$this->request->params['paging'] = $paging;
                    }
                    
					$this->set(compact('posts'));
				}
				else {
					$this->Flash->alert(__d('me_cms', 'You have to wait {0} seconds to perform a new search', config('security.search_interval')));
                }
			}
			else {
				$this->Flash->alert(__d('me_cms', 'You have to search at least a word of {0} characters', 4));
            }
		}
        
        $this->set(compact('pattern'));
	}
	
	/**
     * Views post
	 * @param string $slug Post slug
	 * @uses MeCms\Model\Table\PostsTable::getRelated()
	 */
    public function view($slug = NULL) {
		$post = $this->Posts->find('active')
			->contain([
				'Categories' => ['fields' => ['title', 'slug']],
				'Tags',
				'Users' => ['fields' => ['first_name', 'last_name']],
			])
			->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
			->where([sprintf('%s.slug', $this->Posts->alias()) => $slug])
			->cache(sprintf('view_%s', md5($slug)), $this->Posts->cache)
			->firstOrFail();
        
        $this->set(compact('post'));
        
		//Gets related posts
		if(config('post.related.limit')) {
			$this->set('related', $this->Posts->getRelated($post, config('post.related.limit'), config('post.related.images')));
        }
	}
    
    /**
     * Preview for posts.
     * It uses the `view` template.
	 * @param string $slug Post slug
	 * @uses MeCms\Model\Table\PostsTable::getRelated()
     */
    public function preview($slug = NULL) {
        $post = $this->Posts->find()
			->contain([
				'Categories' => ['fields' => ['title', 'slug']],
				'Tags',
				'Users' => ['fields' => ['first_name', 'last_name']],
			])
			->select(['id', 'title', 'subtitle', 'slug', 'text', 'active', 'created', 'modified'])
			->where([sprintf('%s.slug', $this->Posts->alias()) => $slug])
			->firstOrFail();
        
        $this->set(compact('post'));
        
		//Gets related posts
		if(config('post.related.limit')) {
			$this->set('related', $this->Posts->getRelated($post, config('post.related.limit'), config('post.related.images')));
        }
        
        $this->render('view');
    }
}