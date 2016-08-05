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
namespace MeCms\View\View;

use MeCms\View\View\BaseView;

/**
 * Application view class for admin views
 */
class AdminView extends BaseView {
	/**
     * Initialization hook method
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_initialize
	 * @uses MeCms\View\View\BaseView::initialize()
	 */
    public function initialize() {
		parent::initialize();
		
		//Loads helpers
		$this->loadHelper('MeCms.MenuBuilder');
	}
	
    /**
     * Renders view for given template file and layout
     * @param string|null $view Name of view file to use
     * @param string|null $layout Layout to use
     * @return Rendered content or null if content already rendered and 
     *  returned earlier
     * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_render
     */
    public function render($view = NULL, $layout = NULL) {        
		//Sets some view vars
		$this->set('priorities', [
			'1' => sprintf('1 - %s', __d('me_cms', 'Very low')),
			'2' => sprintf('2 - %s', __d('me_cms', 'Low')),
			'3' => sprintf('3 - %s', __d('me_cms', 'Normal')),
			'4' => sprintf('4 - %s', __d('me_cms', 'High')),
			'5' => sprintf('5 - %s', __d('me_cms', 'Very high'))
		]);
        
        return parent::render($view, $layout);
    }
    
	/**
	 * Renders a layout. Returns output from _render(). Returns false on error. 
     *  Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the 
     *  surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_renderLayout
	 */
	public function renderLayout($content, $layout = NULL) {
		$layout = 'MeCms.admin';
        
		return parent::renderLayout($content, $layout);
	}
}