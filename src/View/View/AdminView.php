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

/**
 * Application view class for admin views
 */
class AdminView extends AppView {
	/**
	 * Renders view for given view file and layout
	 * @param string|NULL $view Name of view file to use
	 * @param string|NULL $layout Layout to use
	 * @return string|NULL Rendered content or NULL if content already rendered and returned earlier
	 * @see http://api.cakephp.org/3.0/class-Cake.View.View.html#_render
     * @throws Cake\Core\Exception\Exception
	 */
	public function render($view = NULL, $layout = NULL) {		
		//Sets the admin layout
		$this->layout = 'backend';
		
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
}