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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Helper
 */

App::uses('MeHtmlHelper', 'MeTools.View/Helper');

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
	 * @uses AuthHelper::isAdmin()
	 * @uses AuthHelper::isManager()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	protected function _banners($type) {
		//Only admins and managers can access these controllers
		if(!$this->Auth->isManager())
			return array();
		
		$menu = array(
			$this->link(__d('me_cms', 'List banners'),	array('controller' => 'banners', 'action' => 'index',	'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Upload banner'),	array('controller' => 'banners', 'action' => 'upload',	'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Add banner'),	array('controller' => 'banners', 'action' => 'add',		'plugin' => 'me_cms'))
		);
		
		//Only admin can access this controller
		if($this->Auth->isAdmin()) {
			$menu[] = $this->link(__d('me_cms', 'List positions'),	array('controller' => 'banners_positions', 'action' => 'index', 'plugin' => 'me_cms'));
			$menu[] = $this->link(__d('me_cms', 'Add position'),	array('controller' => 'banners_positions', 'action' => 'add',	'plugin' => 'me_cms'));
		}
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Banners'), array('icon' => 'dollar')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "pages" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses AuthHelper::isManager()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	protected function _pages($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List pages'), array('controller' => 'pages', 'action' => 'index', 'plugin' => 'me_cms'))
		);
		
		//Only admins and manages can add pages
		if($this->Auth->isManager())
			$menu[] = $this->link(__d('me_cms', 'Add page'), array('controller' => 'pages', 'action' => 'add', 'plugin' => 'me_cms'));
		
		$menu[] = $this->link(__d('me_cms', 'List static pages'), array('controller' => 'pages', 'action' => 'index_statics', 'plugin' => 'me_cms'));
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Pages'), array('icon' => 'files-o')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "photos" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	protected function _photos($type) {
		$menu = array(
			$this->link(__d('me_cms', 'Upload photos'),	array('controller' => 'photos',			'action' => 'upload',	'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Add photos'),	array('controller' => 'photos',			'action' => 'add',		'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'List albums'),	array('controller' => 'photos_albums',	'action' => 'index',	'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Add album'),		array('controller' => 'photos_albums',	'action' => 'add',		'plugin' => 'me_cms'))
		);
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Photos'), array('icon' => 'image')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "posts" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses AuthHelper::isManager()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	protected function _posts($type) {
		$menu = array(
			$this->link(__d('me_cms', 'List posts'),	array('controller' => 'posts', 'action' => 'index', 'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Add post'),		array('controller' => 'posts', 'action' => 'add',	'plugin' => 'me_cms'))
		);
		
		//Only admins and managers can access these actions
		if($this->Auth->isManager())
			$menu = am($menu, array(
				$this->link(__d('me_cms', 'List categories'),	array('controller' => 'posts_categories', 'action' => 'index',	'plugin' => 'me_cms')),
				$this->link(__d('me_cms', 'Add category'),		array('controller' => 'posts_categories', 'action' => 'add',	'plugin' => 'me_cms'))
			));
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Posts'), array('icon' => 'thumb-tack')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "users" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses AuthHelper::isAdmin()
	 * @uses AuthHelper::isManager()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	protected function _users($type) {
		//Only admins and managers can access this controller
		if(!$this->Auth->isManager())
			return array();
		
		$menu = array(
			$this->link(__d('me_cms', 'List users'),	array('controller' => 'users', 'action' => 'index', 'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Add user'),		array('controller' => 'users', 'action' => 'add',	'plugin' => 'me_cms'))
		);
		
		//Only admins can access these actions
		if($this->Auth->isAdmin())
			$menu = am($menu, array(
				$this->link(__d('me_cms', 'List groups'),	array('controller' => 'users_groups', 'action' => 'index',	'plugin' => 'me_cms')),
				$this->link(__d('me_cms', 'Add group'),		array('controller' => 'users_groups', 'action' => 'add',	'plugin' => 'me_cms'))
			));
		
		if($type == 'dropdown')
			return $this->Dropdown->link(__d('me_cms', 'Users'), array('icon' => 'users')).PHP_EOL.$this->Dropdown->dropdown($menu);
		
		return $menu;
	}
	
	/**
	 * Internal function to generate the menu for "systems" actions.
	 * @param string $type Type of menu
	 * @return mixed Menu
	 * @uses AuthHelper::isAdmin()
	 * @uses DropdownHelper::dropdown()
	 * @uses DropdownHelper::link()
	 */
	protected function _systems($type) {
		//Only admins can access this controller
		if(!$this->Auth->isAdmin())
			return array();
		
		$menu = array(
			$this->link(__d('me_cms', 'Cache and thumbs'),	array('controller' => 'systems', 'action' => 'cache',	'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'System checkup'),	array('controller' => 'systems', 'action' => 'checkup', 'plugin' => 'me_cms')),
			$this->link(__d('me_cms', 'Media browser'),		array('controller' => 'systems', 'action' => 'browser', 'plugin' => 'me_cms'))
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
	 */
	public function get($name, $type = NULL) {
		//Dynamic call to the method that generates the requested menu
		$name = sprintf('_%s', $name);
		
		//Checks if the method exists
		if(!method_exists($class = get_called_class(), $name))
			throw new InternalErrorException(__d('me_cms', 'The %s method does not exist', sprintf('%s::%s()', $class, $name)));
		
		$menu = $class::$name($type);
			
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