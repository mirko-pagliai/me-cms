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
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use MeTools\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
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
			->cache('categories_index', $this->PostsCategories->cache)
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
			return $this->redirect([$this->request->query('q')]);
		
		//Checks if the cache is valid
		$this->PostsCategories->Posts->checkIfCacheIsValid();
		
		//Sets the cache name
		$cache = sprintf('index_category_%s_limit_%s_page_%s', md5($category), $this->paginate['limit'], $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->PostsCategories->cache));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
			$query = $this->PostsCategories->Posts->find('active')
				->contain([
					'Categories'	=> ['fields' => ['title', 'slug']],
					'Tags',
					'Users'			=> ['fields' => ['first_name', 'last_name']]
				])
				->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
				->where(['Categories.slug' => $category])
				->order([sprintf('%s.created', $this->PostsCategories->Posts->alias()) => 'DESC']);
					
			if($query->isEmpty())
				throw new RecordNotFoundException(__d('me_cms', 'Record not found'));
					
			$posts = $this->paginate($query)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->PostsCategories->cache);
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
		
		$this->set(compact('posts'));
		
		//Renders on a different view
		$this->render('Posts/index');
	}
}