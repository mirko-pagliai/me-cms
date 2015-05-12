<?php
/**
 * PostsWidgetComponent
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
 * @package		MeCms\Controller\Component
 */

App::uses('Component', 'Controller');

/**
 * Posts widgets
 */
class PostsWidgetComponent extends Component {
	/**
	 * Categories list widget
	 * @return array Categories list
	 */
	public function categories() {
		//Tries to get data from the cache
		$categories = Cache::read($cache = 'widget_categories', 'posts');
		
		//If the data are not available from the cache
        if(empty($categories)) {
			//Loads the `PostsCategory` model
			$this->PostsCategory = ClassRegistry::init('MeCms.PostsCategory');
			
			//Gets the categories
			$categoriesTmp = $this->PostsCategory->find('active', array('fields' => array('id', 'slug', 'post_count')));
			
			if(empty($categoriesTmp))
				return array();

			//Gets the tree list
			$treeList = $this->PostsCategory->generateTreeList();
			
			$categories = array();
			
			foreach($categoriesTmp as $category) {
				//Changes the title, replacing it with the titles of the tree list and adding the "post_count" value
				$category['PostsCategory']['title'] = sprintf('%s (%d)', $treeList[$category['PostsCategory']['id']], $category['PostsCategory']['post_count']);

				//The new array has the slug as key and the title as value
				$categories[$category['PostsCategory']['slug']] = $category['PostsCategory']['title'];
			}
			
            Cache::write($cache, $categories, 'posts');
        }
		
		return $categories;
	}
	
	/**
	 * Latest posts widget
	 * @return array Latest posts
	 */
	public function latest() {
		$options = array_values(func_get_args())[0];
		
		$limit = empty($options['limit']) ? 10 : $options['limit'];
		
		//Tries to get data from the cache
		$posts = Cache::read($cache = sprintf('widget_latest_%d', $limit), 'posts');
		
		//If the data are not available from the cache
        if(empty($posts)) {
			//Loads the `Post` model
			$this->Post = ClassRegistry::init('MeCms.Post');
			
            $posts = $this->Post->find('active', am(array('fields' => array('slug', 'title')), compact('limit')));
			
            Cache::write($cache, $posts, 'posts');
        }
		
		return $posts;
	}
}