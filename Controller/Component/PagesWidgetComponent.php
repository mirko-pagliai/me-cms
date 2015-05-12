<?php
/**
 * PagesWidgetComponent
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
 * Pages widgets
 */
class PagesWidgetComponent extends Component {	
	/**
	 * Pages list widget
	 * @return array Pages list
	 */
	public function pages() {
		//Tries to get data from the cache
		$pages = Cache::read($cache = 'widget_list', 'pages');
		
		//If the data are not available from the cache
        if(empty($pages)) {
			//Loads the `Page` model
			$this->Page = ClassRegistry::init('MeCms.Page');
			
            $pages = $this->Page->find('active', array('fields' => array('title', 'slug')));
			
            Cache::write($cache, $pages, 'pages');
        }
		
		return $pages;
	}
}