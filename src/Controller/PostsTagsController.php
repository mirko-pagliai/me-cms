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

use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use MeCms\Controller\AppController;

/**
 * PostsTags controller
 * @property \MeCms\Model\Table\PostsTagsTable $PostsTags
 */
class PostsTagsController extends AppController {
	/**
	 * Lists posts tags
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
	public function index() {
		//Checks if the cache is valid
		$this->PostsTags->Posts->checkIfCacheIsValid();
		
		$this->set('tags', $this->PostsTags->Tags->find()
			->order(['tag' => 'ASC'])
			->where(['post_count >' => 0])
			->cache('tag_index', $this->PostsTags->Posts->cache)
			->all());
	}
	
	/**
	 * Lists posts for a tag
	 * @param string $tag Tag name
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
    public function view($tag) {
		//Checks if the cache is valid
		$this->PostsTags->Posts->checkIfCacheIsValid();
		
		//Sets the initial cache name
		$cache = sprintf('index_tag_%s', md5($tag));
				
		//Updates the cache name with the query limit and the number of the page
		$cache = sprintf('%s_limit_%s', $cache, $this->paginate['limit']);
		$cache = sprintf('%s_page_%s', $cache, $this->request->query('page') ? $this->request->query('page') : 1);
		
		//Tries to get data from the cache
		list($posts, $paging) = array_values(Cache::readMany([$cache, sprintf('%s_paging', $cache)], $this->PostsTags->cache));
		
		//If the data are not available from the cache
		if(empty($posts) || empty($paging)) {
			$query = $this->PostsTags->Posts->find('active')
				->contain([
					'Categories'	=> ['fields' => ['title', 'slug']],
					'Tags',
					'Users'			=> ['fields' => ['first_name', 'last_name']]
				])
				->matching('Tags', function ($q) use ($tag) {
					return $q->where(['Tags.tag' => str_replace('-', ' ', $tag)]);
				})
				->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
				->order([sprintf('%s.created', $this->PostsTags->Posts->alias()) => 'DESC']);
					
			if($query->isEmpty())
				throw new RecordNotFoundException(__d('me_cms', 'Record not found'));
					
			$posts = $this->paginate($query)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], $this->PostsTags->cache);
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
		
		$this->set(compact('posts'));
		
		//Renders on a different view
		$this->render('Posts/index');
    }
}