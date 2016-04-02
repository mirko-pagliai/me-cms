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
namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * MenuBuilder Helper
 * 
 * It contains methods to renders the backend menus.
 */
class MenuBuilderHelper extends Helper {
	/**
	 * Helpers
	 * @var array
	 */
	public $helpers = ['MeTools.Dropdown', 'Html' => ['className' => 'MeTools.Html']];
	
	/**
	 * Internal function to render a menu as "collapse"
	 * @param string $title The content to be wrapped by <a> tags
	 * @param array $options Array of options and HTML attributes
	 * @param array $menu Menu
	 * @return string Html code
	 * @uses MeTools\View\Helper\HtmlHelper::div()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	protected function _renderAsCollapse($title, array $options = [], $menu) {
		//Sets the collapse name
		$collapseName = sprintf('collapse-%s', strtolower($title));
		
		return $this->Html->div('panel', implode(PHP_EOL, [
			$this->Html->link($title, sprintf('#%s', $collapseName), am($options, [
				'aria-controls'	=> $collapseName,
				'aria-expanded'	=> 'false',
				'class'			=> 'collapsed',
				'data-toggle'	=> 'collapse',
			])),
			$this->Html->div('collapse', implode(PHP_EOL, $menu), ['id' => $collapseName])
		]));
	}

	/**
	 * Internal function to render a menu as "dropdown"
	 * @param string $title The content to be wrapped by <a> tags
	 * @param array $options Array of options and HTML attributes
	 * @param array $menu Menu
	 * @return string Html code
	 * @uses MeTools\View\Helper\DropdownHelper::menu()
	 */
	protected function _renderAsDropdown($title, array $options = [], $menu) {
		return $this->Html->li($this->Dropdown->menu($title, $options, $menu));
	}
	
	/**
	 * Internal function to render a menu as "list"
	 * @param string $title The content to be wrapped by <a> tags
	 * @param array $options Array of options and HTML attributes
	 * @param array $menu Menu
	 * @return string Html code
	 * @uses MeTools\View\Helper\HtmlHelper::ul()
	 */
	protected function _renderAsList($title, array $options = [], $menu) {
		return $this->Html->ul($menu);
	}
	
	/**
	 * Renders all menus for a plugin
	 * @param string $plugin Plugin name
	 * @param string $type Type (`collapse`, `dropdown` or `list`)
	 * @return string Html code
	 * @uses MeTools\Core\Plugin::path()
	 * @uses render()
	 */
	public function all($plugin = 'MeCms', $type = 'collapse') {
		$file = \MeTools\Core\Plugin::path($plugin, 'src'.DS.'View'.DS.'Helper'.DS.$plugin.'MenuHelper.php');

		//Checks if the file is readable
		if(!is_readable($file))
			return;

		//Gets all public methods from the file
		preg_match_all('/\h*public\h+function\h+_(\w+)\(\)\h+\{/', file_get_contents($file), $matches);

		if(empty($matches[1]))
			return;

		foreach($matches[1] as $menu)
			$menus[] = $this->render(sprintf('%s.%s', $plugin, $menu), $type);
		
		return implode(PHP_EOL, $menus);
	}

	/**
	 * Renders a menu
	 * @param string $name Menu name (with plugin notation, eg. `MeCms.posts`)
	 * @param string $type Type (`collapse`, `dropdown` or `list`)
	 * @return string Html code
	 */
	public function render($name, $type = 'collapse') {
		list($plugin, $name) = pluginSplit($name);
		
		$helper = sprintf('%sMenu', $plugin);
		
		//Loads the menu helper
		if(empty($this->{$helper}))
			$this->{$helper} = $this->_View->loadHelper(sprintf('%s.%s', $plugin, $helper));
		
		//Calls dynamically the method from the menu helper
		list($menu, $title, $options) = $this->{$helper}->{sprintf('_%s', $name)}();
		
        if(empty($menu) || empty($title))
            return;
        
		//Calls dynamically the internal render method
		return $this->{sprintf('_renderAs%s', ucfirst($type))}($title, $options, $menu);
	}
}