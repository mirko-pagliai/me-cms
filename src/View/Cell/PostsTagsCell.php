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
	 * @param \Cake\Network\Request $request The request to use in the cell
	 * @param \Cake\Network\Response $response The request to use in the cell
	 * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
	 * @param array $cellOptions Cell options to apply
	 * @uses Cake\View\Cell::__construct()
	 */
	public function __construct(\Cake\Network\Request $request = NULL, \Cake\Network\Response $response = NULL, \Cake\Event\EventManager $eventManager = NULL, array $cellOptions = []) {
		parent::__construct($request, $response, $eventManager, $cellOptions);
		
		$this->loadModel('MeCms.Tags');
	}
	
	/**
	 * Popular tags widgets
	 * @param int $limit Limit
	 * @param string $prefix Prefix for each tag
     * @param string $render Render type (`cloud`, `form` or `list`)
	 * @param bool $shuffle Shuffles tags
	 * @param array|bool $style Applies style to tags
	 */
	public function popular($limit = 10, $prefix = '#', $render = 'cloud', $shuffle = TRUE, array $style = ['maxFont' => 40, 'minFont' => 12]) {
		//Returns on tags index
		if($this->request->is('here', ['_name' => 'posts_tags'])) {
			return;
        }
		
		//Sets the initial cache name
		$cache = sprintf('widget_tags_popular_%s', $limit);
		
		//Updates the cache name
		if(!empty($style['maxFont']) || !empty($style['minFont'])) {
			//Maximum font size we want to use
			$maxFont = empty($style['maxFont']) ? 40 : $style['maxFont'];
			//Minimum font size we want to use
			$minFont = empty($style['minFont']) ? 12 : $style['minFont'];
			
			$cache = sprintf('%s_max_%s_min_%s', $cache, $maxFont, $minFont);
		}
        
        $tags = $this->Tags->find()
            ->select(['tag', 'post_count'])
            ->limit($limit)
            ->order(['post_count' => 'DESC'])
            ->cache($cache, $this->Tags->Posts->cache)
            ->toArray();
        
        if(empty($tags)) {
            return;
        }

		if(!empty($style['maxFont']) || !empty($style['minFont'])) {
            //Number of occurrences of the tag with the highest number of occurrences
            $maxCount = $tags[0]->post_count;
            //Number of occurrences of the tag with the lowest number of occurrences
            $minCount = end($tags)->post_count;

            //Adds the proportional font size to each tag
            $tags = array_map(function($tag) use ($maxCount, $minCount, $maxFont, $minFont) {
                $tag->size = round((($tag->post_count - $minCount) / ($maxCount - $minCount) * ($maxFont - $minFont)) + $minFont);
                return $tag;
            }, $tags);
        }
		
		if($shuffle) {
			shuffle($tags);
        }
        
        //Takes place here, because shuffle() re-indexes
        foreach($tags as $k => $tag) {
            $tags[$tag->slug] = $tag;
            unset($tags[$k]);
        }
        
		$this->set(compact('prefix', 'tags'));
        
        if($render !== 'cloud') {
            $this->viewBuilder()->template(sprintf('popular_as_%s', $render));
        }
	}
}