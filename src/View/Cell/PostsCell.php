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
namespace MeCms\View\Cell;

use Cake\Cache\Cache;
use Cake\View\Cell;

/**
 * Posts cell
 */
class PostsCell extends Cell {
	/**
	 * Constructor. It loads the model
	 * @param \MeTools\Network\Request $request The request to use in the cell
	 * @param \Cake\Network\Response $response The request to use in the cell
	 * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
	 * @param array $cellOptions Cell options to apply
	 * @uses Cake\View\Cell::__construct()
	 */
	public function __construct(\MeTools\Network\Request $request = NULL, \Cake\Network\Response $response = NULL, \Cake\Event\EventManager $eventManager = NULL, array $cellOptions = []) {
		parent::__construct($request, $response, $eventManager, $cellOptions);
		
		$this->loadModel('MeCms.Posts');
	}
	
	/**
	 * Categories widget
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 * @uses MeTools\Network\Request::isCurrent()
	 */
	public function categories() {
		//Returns on categories index
		if($this->request->isCurrent(['_name' => 'posts_categories']))
			return;
		
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();
		
		//Tries to get data from the cache
		$categories = Cache::read($cache = 'widget_categories', $this->Posts->cache);
		
		//If the data are not available from the cache
        if(empty($categories)) {
			foreach($this->Posts->Categories->find('active')
					->select(['title', 'slug', 'post_count'])
					->order(['title' => 'ASC'])
					->toArray() as $k => $category)
				$categories[$category->slug] = sprintf('%s (%d)', $category->title, $category->post_count);
			
            Cache::write($cache, $categories, $this->Posts->cache);
		}
		
		$this->set(compact('categories'));
	}
	
	/**
	 * Latest widget
	 * @param int $limit Limit
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 * @uses MeTools\Network\Request::isAction()
	 */
    public function latest($limit = 10) {
		//Returns on index, except for category
		if($this->request->isAction('index', 'Posts') && !$this->request->param('slug'))
			return;
		
		//Checks if the cache is valid
		$this->Posts->checkIfCacheIsValid();

		$this->set('posts', $this->Posts->find('active')
			->select(['title', 'slug'])
			->limit($limit)
			->order(['created' => 'DESC'])
			->cache(sprintf('widget_latest_%d', $limit), $this->Posts->cache)
			->toArray()
		);
    }
	
	/**
	 * Search widget
	 */
	public function search() {
		//For this widget, control of the action takes place in the view
	}
}