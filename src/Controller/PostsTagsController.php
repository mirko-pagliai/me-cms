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
 * @license	http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Controller;

use Cake\Cache\Cache;
use MeCms\Controller\AppController;

/**
 * PostsTags controller
 * @property \MeCms\Model\Table\PostsTagsTable $PostsTags
 */
class PostsTagsController extends AppController {
	/**
	 * Lists posts for a tag
	 * @param string $tag Tag name
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 */
    public function view($tag) {
		$this->Posts = $this->PostsTags->Posts;
		
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();
		
		//Sets the initial cache name
		$cache = sprintf('index_tag_%s', md5($tag));
				
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
						'Tags',
						'Users'			=> ['fields' => ['first_name', 'last_name']]
					])
					->matching('Tags', function ($q) use ($tag) {
						return $q->where(['Tags.tag' => $tag]);
					})
					->select(['id', 'title', 'subtitle', 'slug', 'text', 'created'])
					->order([sprintf('%s.created', $this->Posts->alias()) => 'DESC'])
			)->toArray();
						
			//Writes on cache
			Cache::writeMany([$cache => $posts, sprintf('%s_paging', $cache) => $this->request->param('paging')], 'posts');
		}
		//Else, sets the paging parameter
		else
			$this->request->params['paging'] = $paging;
		
		$this->set(am(['title' => __d('me_cms', 'Tag {0}', $tag)], compact('posts')));
		
		//Renders on a different view
		$this->render('Posts/index');
    }
}