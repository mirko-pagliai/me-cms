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
class PostsTagsCell extends Cell {
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
		
		$this->loadModel('MeCms.Tags');
	}
	
	/**
	 * Popular tags widgets
	 * @param int $limit Limit
	 * @param array|bool $style Applies style to tags
	 * @param bool $shuffle Shuffles tags
	 * @uses MeCms\Model\Table\PostsTable::checkIfCacheIsValid()
	 * @uses MeTools\Network\Request::isCurrent()
	 */
	public function popular($limit = 10, array $style = ['maxFont' => 40, 'minFont' => 12], $shuffle = TRUE) {
		//Returns on tags index
		if($this->request->isCurrent(['_name' => 'posts_tags']))
			return;
		
		//Sets the initial cache name
		$cache = sprintf('widget_tags_popular_%s', $limit);
		
		//Updates the cache name
		if($style) {
			//Maximum font size we want to use
			$maxFont = empty($style['maxFont']) ? 40 : $style['maxFont'];
			//Minimum font size we want to use
			$minFont = empty($style['minFont']) ? 12 : $style['minFont'];
			
			$cache = sprintf('%s_max_%s_min_%s', $cache, $maxFont, $minFont);
		}
		
		//Checks if the cache is valid
		$this->Tags->Posts->checkIfCacheIsValid();
		
		//Tries to get data from the cache
		$tags = Cache::read($cache, $this->Tags->Posts->cache);
		
		//If the data are not available from the cache
        if(empty($tags)) {
			$tags = $this->Tags->find()
				->select(['tag', 'post_count'])
				->limit($limit)
				->order(['post_count' => 'DESC'])
				->toArray();

			if($style) {
				//Number of occurrences of the tag with the highest number of occurrences
				$maxCount = $tags[0]['post_count'];
				//Number of occurrences of the tag with the lowest number of occurrences
				$minCount = end($tags)['post_count'];

				//Adds the proportional font size to each tag
				$tags = array_map(function($tag) use ($maxCount, $minCount, $maxFont, $minFont) {
					$tag['size'] = round((($tag['post_count'] - $minCount) / ($maxCount - $minCount) * ($maxFont - $minFont)) + $minFont);
					return $tag;
				}, $tags);
			}
			
            Cache::write($cache, $tags, $this->Tags->Posts->cache);
		}
		
		if($shuffle)
			shuffle($tags);
		
		$this->set(compact('tags'));
	}
}