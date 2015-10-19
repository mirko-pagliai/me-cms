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
namespace MeCms\View\View;

use MeCms\View\View\AppView;
use MeTools\Core\Plugin;

/**
 * Application view class for admin views
 */
class AdminView extends AppView {
	/**
	 * Gets the menus for the backend
	 * @return array Menus
	 * @uses MeTools\Core\Plugin::getAll()
	 * @uses MeTools\Core\Plugin::path()
	 */
	protected function getMenus() {
		$menus = [];
		
		foreach(Plugin::getAll(['DebugKit', 'MeTools', 'Migrations']) as $plugin) {
			//Checks if the file is readable
			if(!is_readable($file = Plugin::path($plugin, 'src'.DS.'View'.DS.'Helper'.DS.'MenuDefaultHelper.php')))
				continue;
			
			//Gets all public methods
			if(!preg_match_all('/\h*public\h+function\h+(_\w+)\(\)\h+\{/', @file_get_contents($file), $matches))
				continue;
			
			//Loads the menu helper
			$this->MenuDefault = $this->helpers()->load(sprintf('%s.MenuDefault', $plugin));
			
			//Automatically calls each dynamic method that generates the requested menu
			foreach($matches[1] as $method)
				$menus[sprintf('%s_menu', $plugin === 'MeCms' ? 'mecms' : 'plugins')][] = $this->MenuDefault->{$method}();
			
			//Unloads the helper
			$this->helpers()->unload('MenuDefault');
		}
		
		return $menus;
	}

	/**
     * Initialization hook method
	 * @see http://api.cakephp.org/3.1/class-Cake.View.View.html#_initialize
	 * @uses MeCms\View\View::initialize()
	 */
    public function initialize() {
		parent::initialize();
		
		//Loads helpers
		$this->loadHelper('MeCms.MenuBuilder');
	}
	
	/**
	 * Renders view for given view file and layout
	 * @param string|NULL $view Name of view file to use
	 * @param string|NULL $layout Layout to use
	 * @return string|NULL Rendered content or NULL if content already rendered and returned earlier
	 * @see http://api.cakephp.org/3.1/class-Cake.View.View.html#_render
     * @throws Cake\Core\Exception\Exception
	 * @uses MeCms\View\View\AppView::render()
	 * @uses layout
	 * @uses viewVars
	 */
	public function render($view = NULL, $layout = NULL) {		
		//Sets the admin layout
		$this->layout = 'MeCms.backend';
		
		//Sets some view vars
		$this->viewVars['priorities'] = [
			'1' => sprintf('1 - %s', __d('me_cms', 'Very low')),
			'2' => sprintf('2 - %s', __d('me_cms', 'Low')),
			'3' => sprintf('3 - %s', __d('me_cms', 'Normal')),
			'4' => sprintf('4 - %s', __d('me_cms', 'High')),
			'5' => sprintf('5 - %s', __d('me_cms', 'Very high'))
		];
		
		return parent::render($view, $layout);
	}
	
	/**
	 * Renders a layout. Returns output from _render(). Returns false on error. Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.1/source-class-Cake.View.View.html#477-513
     * @throws Cake\Core\Exception\Exception
	 * @uses MeCms\View\View\AppView::renderLayout()
	 * @uses getMenu()
	 * @uses viewVars
	 */
	public function renderLayout($content, $layout = NULL) {
		//Sets the menus view vars
		$this->viewVars += $this->getMenus();
		
		return parent::renderLayout($content, $layout);
	}
}