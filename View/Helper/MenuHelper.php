<?php
/**
 * MenuHelper.
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Helper
 */

/**
 * Menu Helper.
 * 
 * It can be used to generate a menu for an action. It supports these types of menu: `ul`, `nav` and `dropdown`.
 * 
 * To generate a menu, you have to use the `get` method. For example:
 * <code>
 * $this->Menu->get('photos', 'dropdown')
 * </code>
 */
class MenuHelper extends MeHtmlHelper {
	/**
	 * Internal function to generate the menu for "pages" actions
	 * @return array
	 */
	private function _pages($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List pages'),	array('controller' => 'pages', 'action' => 'index')),
			$this->link(__d('me_cms', 'Add page'),		array('controller' => 'pages', 'action' => 'add'))
		);
		
		if($type == 'dropdown')
			return $this->linkDropdown(__d('me_cms', 'Pages'), array('icon' => 'files-o')).PHP_EOL.$this->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "post" actions
	 * @return array
	 */
	private function _posts($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List posts'),		array('controller' => 'posts',				'action' => 'index')),
			$this->link(__d('me_cms', 'Add post'),			array('controller' => 'posts',				'action' => 'add')),
			$this->link(__d('me_cms', 'List categories'),	array('controller' => 'posts_categories',	'action' => 'index')),
			$this->link(__d('me_cms', 'Add category'),		array('controller' => 'posts_categories',	'action' => 'add'))
		);
		
		if($type == 'dropdown')
			return $this->linkDropdown(__d('me_cms', 'Posts'), array('icon' => 'thumb-tack')).PHP_EOL.$this->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "photos" actions
	 * @return array
	 */
	private function _photos($type) {
		$menu = array(
			$this->link(__d('me_cms', 'Add photos'),	array('controller' => 'photos',			'action' => 'add')),
			$this->link(__d('me_cms', 'List albums'),	array('controller' => 'photos_albums',	'action' => 'index')),
			$this->link(__d('me_cms', 'Add album'),		array('controller' => 'photos_albums',	'action' => 'add'))
		);
		
		if($type == 'dropdown')
			return $this->linkDropdown(__d('me_cms', 'Photos'), array('icon' => 'image')).PHP_EOL.$this->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "users" actions
	 * @return array
	 */
	private function _users($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List users'),	array('controller' => 'users',			'action' => 'index')),
			$this->link(__d('me_cms', 'Add user'),		array('controller' => 'users',			'action' => 'add')),
			$this->link(__d('me_cms', 'List groups'),	array('controller' => 'users_groups',	'action' => 'index')),
			$this->link(__d('me_cms', 'Add group'),		array('controller' => 'users_groups',	'action' => 'add'))
		);
		
		if($type == 'dropdown')
			return $this->linkDropdown(__d('me_cms', 'Users'), array('icon' => 'users')).PHP_EOL.$this->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Generates and returns a menu for an action.
	 * @param string $name Name of the action for which to generate the menu
	 * @param string $type Type of menu (optional, `ul`, `nav` or `dropdown`)
	 * @return mixed
	 * @uses _pages() to generate the menu for "pages" actions
	 * @uses _photos() to generate the menu for "photos" actions
	 * @uses _posts() to generate the menu for "posts" actions
	 * @uses _users() to generate the menu for "users" actions
	 */
	public function get($name, $type = NULL) {
		//Dynamic call to the method that generates the requested menu
		$name = sprintf('_%s', $name);
		$menu = $this->$name($type);
			
		//Switch the type of menu
		switch($type) {
			case 'ul':
				$menu = $this->ul($menu);
				break;
			case 'nav':
				$menu = $this->ul($menu, array('class' => 'nav'));
				break;
		}
		
		return $menu;
	}
}