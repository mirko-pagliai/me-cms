<?php
/**
 * WidgetHelper.
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

/**
 * Widget Helper.
 * 
 * This helper allows you to use widgets.
 */
class WidgetHelper extends AppHelper {
	/**
	 * Renders a widget.
	 * @param array $widget Widget, as array with `name` and (optional) `options` keys
	 * @return string Html, element
	 * @throws InternalErrorException
	 */
	public function render($widget) {
		if(empty($widget['name']))
			throw new InternalErrorException(__d('me_cms', 'Invalid widget name'));
		
		list($plugin, $name) = pluginSplit($widget['name']);
			
		$element = empty($plugin) ? sprintf('widgets/%s', $name) : sprintf('%s.widgets/%s', $plugin, $name);
		
		//Checks if the widget exists
		if(!$this->_View->elementExists($element))
			throw new InternalErrorException(__d('me_cms', 'The widget %s doesn\'t exist', $widget['name']));
			
		return $this->_View->element($element, array('options' => empty($widget['options']) ? array() : $widget['options']));
	}
}