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
 * Photos cell
 */
class PhotosCell extends Cell {
	/**
	 * Constructor
	 * @param \MeTools\Network\Request $request The request to use in the cell
	 * @param \Cake\Network\Response $response The request to use in the cell
	 * @param \Cake\Event\EventManager $eventManager The eventManager to bind events to
	 * @param array $cellOptions Cell options to apply
	 * @uses Cake\View\Cell::__construct()
	 */
	public function __construct(\MeTools\Network\Request $request = NULL, \Cake\Network\Response $response = NULL, \Cake\Event\EventManager $eventManager = NULL, array $cellOptions = []) {
		parent::__construct($request, $response, $eventManager, $cellOptions);
		
		//Loads the Photos model
		$this->loadModel('MeCms.Photos');
	}
	
	/**
	 * Albums widget
	 * @uses MeTools\Network\Request::isController()
	 */
	public function albums() {
		//Returns on the same controllers
		if($this->request->isController(['Photos', 'PhotosAlbums']))
			return;
		
		//Tries to get data from the cache
		$albums = Cache::read($cache = 'widget_albums', $this->Photos->cache);
		
		//If the data are not available from the cache
        if(empty($albums)) {
			foreach($this->Photos->Albums->find('active')
						->select(['title', 'slug', 'photo_count'])
						->order(['title' => 'ASC'])
						->toArray() as $k => $album)
					$albums[$album->slug] = sprintf('%s (%d)', $album->title, $album->photo_count);
			
            Cache::write($cache, $albums, $this->Photos->cache);
		}
		
		$this->set(compact('albums'));
	}
	
	/**
	 * Latest widget
	 * @param int $limit Limit
	 * @uses MeTools\Network\Request::isController()
	 */
	public function latest($limit = 1) {
		//Returns on the same controllers
		if($this->request->isController(['Photos', 'PhotosAlbums']))
			return;
				
		//Gets and sets photos
		$this->set('photos', $this->Photos->find('active')
			->select(['album_id', 'filename'])
			->limit($limit)
			->order([sprintf('%s.id', $this->Photos->alias()) => 'DESC'])
			->cache(sprintf('widget_latest_%d', $limit), $this->Photos->cache)
			->toArray()
		);
	}
	
	/**
	 * Random widget
	 * @param int $limit Limit
	 * @uses MeTools\Network\Request::isController()
	 */
	public function random($limit = 1) {
		//Returns on the same controllers
		if($this->request->isController(['Photos', 'PhotosAlbums']))
			return;
		
		//Returns, if there are no records available
		if(Cache::read($cache = 'no_photos', $this->Photos->cache))
			return;
		
		//Gets photos
		$photos = $this->Photos->find('active')
			->select(['album_id', 'filename'])
			->limit($limit)
			->order('rand()')
			->toArray();
		
		//Writes on cache, if there are no records available
		if(empty($photos))
			Cache::write($cache, TRUE, $this->Photos->cache);
		
		$this->set(compact('photos'));
	}
}