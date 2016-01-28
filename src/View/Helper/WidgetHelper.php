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
 * Widget Helper
 * 
 * It contains methods to render widgets.
 */
class WidgetHelper extends Helper {
	/**
	 * Renders all widgets, reading from configuration
	 * @return string Html code
	 * @uses MeTools\Network\Request::isCurrent()
	 * @uses widget()
	 */
	public function all() {
		if($this->request->isCurrent(['_name' => 'homepage']) && config('frontend.widgets.homepage'))
			$widgets = config('frontend.widgets.homepage');
		else
			$widgets = config('frontend.widgets.general');
			

		foreach($widgets as $name => $args)
			$widgets[$name] = is_array($args) ? $this->widget($name, $args) : $this->widget($args);

		return implode(PHP_EOL, $widgets);
	}
	
	/**
	 * Returns a widget
	 * @param string $name Widget name
	 * @param array $arguments Widget arguments
	 * @param array $options Widget options
	 * @return Cake\View\Cell The cell instance
	 * @uses Cake\View\CellTrait::cell()
	 */
	public function widget($name, array $arguments = [], array $options = []) {
		return $this->_View->cell($name, $arguments, $options);
	}
}