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

use Cake\View\Cell;

/**
 * Pages cell
 */
class PagesCell extends Cell {
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
		$this->loadModel('MeCms.Pages');
	}
	
	/**
	 * Pages list widget
	 * @uses MeTools\Network\Request::isHere()
	 */
	public function pages() {
		//Returns on pages index
		if($this->request->isHere(['_name' => 'pages'])) {
			return;
        }

		$pages = $this->Pages->find('active')
			->select(['title', 'slug'])
			->order(['title' => 'ASC'])
			->cache(sprintf('widget_list'), $this->Pages->cache)
			->toArray();
        
        $this->set(compact('pages'));
	}
}