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

use Cake\Network\Exception\InternalErrorException;
use Cake\View\Helper;

/**
 * MenuBuilder Helper
 * 
 * It contains methods to generate menus.
 */
class MenuBuilderHelper extends Helper {
	/**
	 * Helpers
	 * @var array
	 */
	public $helpers = ['MeTools.Dropdown', 'Html' => ['className' => 'MeTools.Html']];
	
	/**
	 * Internal function to render a collapsed menu
	 * @param string $title The content to be wrapped by <a> tags
	 * @param array $options Array of options and HTML attributes
	 * @param array $menu Menu
	 * @return string Html code
	 * @uses MeTools\View\Helper\HtmlHelper::div()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 */
	protected function __collapseMenu($title, array $options = [], $menu) {
		//Sets the collapse name
		$collapseName = sprintf('collapse-%s', strtolower($title));
		
		return implode(PHP_EOL, [
			$this->Html->link($title, sprintf('#%s', $collapseName), am($options, [
				'aria-controls'	=> $collapseName,
				'aria-expanded'	=> 'false',
				'class'			=> 'collapsed',
				'data-toggle'	=> 'collapse',
			])),
			$this->Html->div('collapse', implode(PHP_EOL, $menu), ['id' => $collapseName])
		]);
	}
	
	/**
	 * Generates and returns a menu
	 * @param array $menu Menu data (menu code, title and options)
	 * @param string $type Type of menu (optional, `ul` by default, `collapse` or `dropdown`)
	 * @return string Html code
	 * @uses MeTools\View\Helper\DropdownHelper::menu()
	 * @uses MeTools\View\Helper\HtmlHelper::div()
	 * @uses MeTools\View\Helper\HtmlHelper::ul()
	 */
	public function render($menu, $type = NULL) {
		if(empty($menu[0]) || empty($menu[1]))
			return;

		list($menu, $title, $options) = [$menu[0], $menu[1], empty($menu[2]) ? [] : $menu[2]];
				
		//Switch the type of menu
		switch($type) {
			case 'dropdown':
				return $this->Dropdown->menu($title, $options, $menu);
				break;
			case 'collapse':
				return $this->Html->div('panel', $this->__collapseMenu($title, $options, $menu));
				break;
			default:
				return $this->Html->ul($menu);
				break;
		}
	}
}