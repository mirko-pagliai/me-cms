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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\View\Helper;

use Cake\View\Helper;
use MeCms\View\Helper\AuthHelper;

/**
 * MenuDefault Helper.
 * 
 * This helper contains methods that will be called automatically to generate the menu of the backend.
 * You do not need to call these methods manually.
 */
class MenuDefaultHelper extends Helper {
	/**
	 * Helpers
	 * @var array
	 */
	public $helpers = ['MeCms.Auth', 'Html' => ['className' => 'MeTools.Html']];
	
	/**
	 * Internal function to generate the menu for "posts" actions
	 * @return mixed Array with menu, title and link options
	 * @uses MeCms\View\Helper\AuthHelper::isGroup()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	public function _posts() {
		$menu = [
			$this->Html->link(__d('me_cms', 'List posts'), ['controller' => 'Posts', 'action' => 'index', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'Add post'), ['controller' => 'Posts', 'action' => 'add', 'plugin' => 'MeCms'])
		];
		
		//Only admins and managers can access these actions
		if($this->Auth->isGroup(['admin', 'manager']))
			array_push($menu,
				$this->Html->link(__d('me_cms', 'List categories'),	['controller' => 'PostsCategories', 'action' => 'index', 'plugin' => 'MeCms']),
				$this->Html->link(__d('me_cms', 'Add category'), ['controller' => 'PostsCategories', 'action' => 'add', 'plugin' => 'MeCms'])
			);
		
		return [$menu, __d('me_cms', 'Posts'), ['icon' => 'file-text-o']];
	}
	
	/**
	 * Internal function to generate the menu for "pages" actions
	 * @return mixed Array with menu, title and link options
	 * @uses MeCms\View\Helper\AuthHelper::isGroup()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	public function _pages() {
		$menu = [
			$this->Html->link(__d('me_cms', 'List pages'), ['controller' => 'Pages', 'action' => 'index', 'plugin' => 'MeCms'])
		];
		
		//Only admins and manages can add pages
		if($this->Auth->isGroup(['admin', 'manager']))
			array_push($menu, $this->Html->link(__d('me_cms', 'Add page'), ['controller' => 'Pages', 'action' => 'add', 'plugin' => 'MeCms']));
		
		array_push($menu, $this->Html->link(__d('me_cms', 'List static pages'), ['controller' => 'Pages', 'action' => 'statics', 'plugin' => 'MeCms']));
		
		return [$menu, __d('me_cms', 'Pages'), ['icon' => 'files-o']];
	}
	
	/**
	 * Internal function to generate the menu for "photos" actions
	 * @return mixed Array with menu, title and link options
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	public function _photos() {
		$menu = [
			$this->Html->link(__d('me_cms', 'Upload photos'), ['controller' => 'Photos', 'action' => 'upload', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'List albums'), ['controller' => 'PhotosAlbums', 'action' => 'index', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'Add album'), ['controller' => 'PhotosAlbums', 'action' => 'add', 'plugin' => 'MeCms'])
		];
		
		return [$menu, __d('me_cms', 'Photos'), ['icon' => 'camera-retro']];
	}	
	
	/**
	 * Internal function to generate the menu for "banners" actions
	 * @return mixed Array with menu, title and link options
	 * @uses MeCms\View\Helper\AuthHelper::isGroup()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	public function _banners() {
		//Only admins and managers can access these controllers
		if(!$this->Auth->isGroup(['admin', 'manager']))
			return;
		
		$menu = [
			$this->Html->link(__d('me_cms', 'List banners'), ['controller' => 'banners', 'action' => 'index', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'Upload banners'), ['controller' => 'banners', 'action' => 'upload', 'plugin' => 'MeCms'])
		];
		
		//Only admin can access this controller
		if($this->Auth->isGroup('admin'))
			array_push($menu,
				$this->Html->link(__d('me_cms', 'List positions'), ['controller' => 'banners_positions', 'action' => 'index', 'plugin' => 'MeCms']),
				$this->Html->link(__d('me_cms', 'Add position'), ['controller' => 'banners_positions', 'action' => 'add', 'plugin' => 'MeCms'])
			);
		
		return [$menu, __d('me_cms', 'Banners'), ['icon' => 'shopping-cart']];
	}
	
	/**
	 * Internal function to generate the menu for "users" actions
	 * @return mixed Array with menu, title and link options
	 * @uses MeCms\View\Helper\AuthHelper::isGroup()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	public function _users() {
		//Only admins and managers can access this controller
		if(!$this->Auth->isGroup(['admin', 'manager']))
			return;
		
		$menu = [
			$this->Html->link(__d('me_cms', 'List users'), ['controller' => 'Users', 'action' => 'index', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'Add user'), ['controller' => 'Users', 'action' => 'add', 'plugin' => 'MeCms'])
		];
		
		//Only admins can access these actions
		if($this->Auth->isGroup('admin'))
			array_push($menu,
				$this->Html->link(__d('me_cms', 'List groups'), ['controller' => 'UsersGroups', 'action' => 'index', 'plugin' => 'MeCms']),
				$this->Html->link(__d('me_cms', 'Add group'), ['controller' => 'UsersGroups', 'action' => 'add', 'plugin' => 'MeCms'])
			);
		
		return [$menu, __d('me_cms', 'Users'), ['icon' => 'users']];
	}
	
	/**
	 * Internal function to generate the menu for "systems" actions
	 * @return mixed Array with menu, title and link options
	 * @uses MeCms\View\Helper\AuthHelper::isGroup()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	public function _systems() {
		//Only admins can access this controller
		if(!$this->Auth->isGroup('admin'))
			return;
		
		$menu = [
			$this->Html->link(__d('me_cms', 'Temporary files'), ['controller' => 'Systems', 'action' => 'temporary', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'System checkup'), ['controller' => 'Systems', 'action' => 'checkup', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'Media browser'), ['controller' => 'Systems', 'action' => 'browser', 'plugin' => 'MeCms']),
			$this->Html->link(__d('me_cms', 'Changelogs'), ['controller' => 'Systems', 'action' => 'changelogs', 'plugin' => 'MeCms'])
		];
		
		//Only admins can see logs
		if($this->Auth->isGroup('admin'))
			array_push($menu,
				$this->Html->link(__d('me_cms', 'Log viewer'), ['controller' => 'Systems', 'action' => 'logs', 'plugin' => 'MeCms'])
			);
		
		return [$menu, __d('me_cms', 'System'), ['icon' => 'wrench']];
	}
}