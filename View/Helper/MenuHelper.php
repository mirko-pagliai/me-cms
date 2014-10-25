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
     * Helpers
     * @var array
     */
    public $helpers = array(
		'Auth'		=> array('className' => 'MeCms.Auth'),
		'Dropdown'	=> array('className' => 'MeTools.Dropdown')
	);
	
	/**
	 * Internal function to generate the menu for "banners" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses link()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 * @uses AuthHelper::isAdmin()
	 */
	private function _banners($type) {
		//Only admins can access these controllers
		if(!$this->Auth->isAdmin())
			return array();
		
		$menu = array(
			$this->link(__d('me_cms', 'List banners'),		array('controller' => 'banners', 'action' => 'index')),
			$this->link(__d('me_cms', 'Add banner'),		array('controller' => 'banners', 'action' => 'add')),
			$this->link(__d('me_cms', 'List positions'),	array('controller' => 'banners_positions', 'action' => 'index')),
			$this->link(__d('me_cms', 'Add position'),		array('controller' => 'banners_positions', 'action' => 'add'))
		);
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Banners'), array('icon' => 'dollar')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "pages" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses link()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 * @uses AuthHelper::isManager()
	 */
	private function _pages($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List pages'), array('controller' => 'pages', 'action' => 'index'))
		);
		
		//Only admins and manages can add pages
		if($this->Auth->isManager())
			$menu[] = $this->link(__d('me_cms', 'Add page'), array('controller' => 'pages', 'action' => 'add'));
		
		$menu[] = $this->link(__d('me_cms', 'List static pages'), array('controller' => 'pages', 'action' => 'index_statics'));
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Pages'), array('icon' => 'files-o')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "post" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses link()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 * @uses AuthHelper::isManager()
	 */
	private function _posts($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List posts'),	array('controller' => 'posts', 'action' => 'index')),
			$this->link(__d('me_cms', 'Add post'),		array('controller' => 'posts', 'action' => 'add'))
		);
		
		//Only admins and managers can access these actions
		if($this->Auth->isManager())
			$menu = am($menu, array(
				$this->link(__d('me_cms', 'List categories'),	array('controller' => 'posts_categories', 'action' => 'index')),
				$this->link(__d('me_cms', 'Add category'),		array('controller' => 'posts_categories', 'action' => 'add'))
			));
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Posts'), array('icon' => 'thumb-tack')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "photos" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses link()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	private function _photos($type) {
		$menu = array(
			$this->link(__d('me_cms', 'Add photos'),	array('controller' => 'photos',			'action' => 'add')),
			$this->link(__d('me_cms', 'List albums'),	array('controller' => 'photos_albums',	'action' => 'index')),
			$this->link(__d('me_cms', 'Add album'),		array('controller' => 'photos_albums',	'action' => 'add'))
		);
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Photos'), array('icon' => 'image')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "users" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses link()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 * @uses AuthHelper::isAdmin()
	 * @uses AuthHelper::isManager()
	 */
	private function _users($type) {
		//Only admins and managers can access this controller
		if(!$this->Auth->isManager())
			return array();
		
		$menu = array(
			$this->link(__d('me_cms', 'List users'),	array('controller' => 'users', 'action' => 'index')),
			$this->link(__d('me_cms', 'Add user'),		array('controller' => 'users', 'action' => 'add'))
		);
		
		//Only admins can access these actions
		if($this->Auth->isAdmin())
			$menu = am($menu, array(
				$this->link(__d('me_cms', 'List groups'),	array('controller' => 'users_groups', 'action' => 'index')),
				$this->link(__d('me_cms', 'Add group'),		array('controller' => 'users_groups', 'action' => 'add'))
			));
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Users'), array('icon' => 'users')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "system" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses link()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 * @uses AuthHelper::isAdmin()
	 */
	private function _systems($type) {
		//Only admins can access this controller
		if(!$this->Auth->isAdmin())
			return array();
		
		$menu = array(
			$this->link(__d('me_cms', 'Cache and thumbs'),	array('controller' => 'systems', 'action' => 'cache')),
			$this->link(__d('me_cms', 'System checkup'),	array('controller' => 'systems', 'action' => 'checkup')),
			$this->link(__d('me_cms', 'Media browser'),		array('controller' => 'systems', 'action' => 'browser'))
		);
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'System'), array('icon' => 'wrench')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Generates and returns a menu for an action.
	 * @param string $name Name of the action for which to generate the menu
	 * @param string $type Type of menu (optional, `ul`, `nav` or `dropdown`)
	 * @return mixed Menu
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