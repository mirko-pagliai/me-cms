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
 * PostsCategories controller
 * @property \MeCms\Model\Table\PostsCategoriesTable $PostsCategories
 */
class PostsCategoriesController extends AppController {
	/**
     * Lists posts categories
     */
    public function index() {
		$this->set('categories', $this->PostsCategories->find('active')
			->select(['title', 'slug'])
			->order(['title' => 'ASC'])
			->cache('categories_index', 'posts')
			->all());
    }
	
	/**
	 * Lists posts for a category
	 * @param string $category Category slug
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
	public function view($category = NULL) {
		//The category can be passed as query string, from a widget
		if($this->request->query('q'))
			$this->redirect([$this->request->query('q')]);
		
		//Checks if the cache is valid
		$this->PostsCategories->Posts->checkIfCacheIsValid();
		
		//Sets the initial cache name
		$cache = sprintf('index_category_%s', md5($category));
				
		//Updates the cache name with the query limit and the number of the page
		$cache = sprintf('%s_limit_%s', $cache, $this->paginate['limit']);
		$cache = sprintf('%s_page_%s', $cache, $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], 'posts'));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
			$posts = $this->paginate(
				$this->PostsCategories->Posts->find('active')
					->contain([
						'Categories'	=> ['fields' => ['title', 'slug']],
						'Tags',
						'Users'			=> ['fields' => ['first_name', 'last_name']]
					])
					->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
					->where(['Categories.slug' => $category])
					->order([sprintf('%s.created', $this->PostsCategories->Posts->alias()) => 'DESC'])
			)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], 'posts');
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
		
		$this->set(am([
			'title' => empty($posts[0]->category->title) ? NULL : $posts[0]->category->title
		], compact('posts')));
		
		//Renders on a different view
		$this->render('Posts/index');
	}
}