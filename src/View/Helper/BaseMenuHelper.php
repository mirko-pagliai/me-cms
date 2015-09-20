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
 * BaseMenu Helper
 * 
 * It contains methods to generate menus.
 */
class BaseMenuHelper extends Helper {
	/**
	 * Collapse name. Used for "collapse" menus
	 * @var string 
	 */
	protected $collapseName;
	
	/**
	 * Helpers
	 * @var array
	 */
	public $helpers = ['MeCms.Auth', 'MeTools.Dropdown', 'Html' => ['className' => 'MeTools.Html']];
	
	/**
	 * Internal function to create a collapse menu
	 * @param string $title The content to be wrapped by <a> tags
	 * @param array $options Array of options and HTML attributes
	 * @param array $menu Menu
	 * @return string Html code
	 * @uses MeTools\View\Helper\HtmlHelper::div()
	 * @uses MeTools\View\Helper\HtmlHelper::link()
	 * @uses collapseName
	 */
	protected function __collapseMenu($title, array $options = [], $menu) {
		return implode(PHP_EOL, [
			$this->Html->link($title, sprintf('#%s', $this->collapseName), am($options, [
				'aria-controls'	=> $this->collapseName,
				'aria-expanded'	=> 'false',
				'class'			=> 'collapsed',
				'data-toggle'	=> 'collapse',
			])),
			$this->Html->div('collapse', implode(PHP_EOL, $menu), ['id' => $this->collapseName])
		]);
	}

	/**
	 * Generates and returns a menu for an action
	 * @param string $name Name of the action for which to generate the menu
	 * @param string $type Type of menu (optional, `ul`, `collapse` or `dropdown`)
	 * @return mixed Html or FALSE
	 * @throws InternalErrorException
	 * @uses DropdownHelper::menu()
	 * @uses MeTools\View\Helper\HtmlHelper::div()
	 * @uses MeTools\View\Helper\HtmlHelper::ul()
	 * @uses __collapseMenu()
	 * @uses collapseName
	 */
	public function get($method, $type = NULL) {
		//Sets the collapse name
		$this->collapseName = sprintf('collapse-%s', strtolower($method));
		
		//Checks if the method exists
		if(!method_exists($class = get_called_class(), $method = sprintf('_%s', $method)))
			throw new InternalErrorException(__d('me_cms', 'The {0} method does not exist', sprintf('%s::%s()', $class, $method)));
		
		//Dynamic call to the method that generates the requested menu
		$output = $class::{$method}($type);
		
		if(empty($output[0]) || empty($output[1]))
			return FALSE;
		
		list($menu, $title, $options) = [$output[0], $output[1], empty($output[2]) ? [] : $output[2]];
		
		//Switch the type of menu
		switch($type) {
			case 'ul':
				return $this->ul($menu);
				break;
			case 'dropdown':
				return $this->Dropdown->menu($title, $options, $menu);
				break;
			case 'collapse':
				return $this->Html->div('panel', $this->__collapseMenu($title, $options, $menu));
				break;
		}
	}
}