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

use App\View\AppView as BaseView;
use MeCms\View\View\AppView;

/**
 * Application view class for admin views
 */
class AdminView extends AppView {
	/**
     * Initialization hook method
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_initialize
	 * @uses App\View\AppView::initialize()
	 */
    public function initialize() {
		BaseView::initialize();
		
		//Loads helpers
		$this->loadHelper('Html', ['className' => 'MeTools.Html']);
		$this->loadHelper('MeTools.Dropdown');
		$this->loadHelper('MeTools.Form');
		$this->loadHelper('MeTools.Library');
		$this->loadHelper('MeTools.Paginator');
		$this->loadHelper('Assets.Asset');
		$this->loadHelper('Thumbs.Thumb');
		$this->loadHelper('MeCms.Auth');
		$this->loadHelper('MeCms.MenuBuilder');
	}
	
	/**
	 * Renders view for given view file and layout
	 * @param string|NULL $view Name of view file to use
	 * @param string|NULL $layout Layout to use
	 * @return string|NULL Rendered content or NULL if content already rendered and returned earlier
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_render
	 * @uses App\View\AppView::render()
	 * @uses layout
	 * @uses viewVars
	 */
	public function render($view = NULL, $layout = NULL) {
		//Sets the layout
		if($this->layout === 'default')
			$this->layout = config('backend.layout');
		
		//Sets some view vars
		$this->viewVars['priorities'] = [
			'1' => sprintf('1 - %s', __d('me_cms', 'Very low')),
			'2' => sprintf('2 - %s', __d('me_cms', 'Low')),
			'3' => sprintf('3 - %s', __d('me_cms', 'Normal')),
			'4' => sprintf('4 - %s', __d('me_cms', 'High')),
			'5' => sprintf('5 - %s', __d('me_cms', 'Very high'))
		];
		
		return BaseView::render($view, $layout);
	}
	
	/**
	 * Renders a layout. Returns output from _render(). Returns false on error. Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.2/source-class-Cake.View.View.html#477-513
	 * @uses MeTools\View\Helper\HtmlHelper::meta()
	 * @uses _getTitleForLayout()
	 */
	public function renderLayout($content, $layout = NULL) {
		//Assigns the title for layout
		$this->assign('title', $this->_getTitleForLayout());
		
		//Adds the favicon
		if(is_readable(WWW_ROOT.'favicon.ico'))
			$this->Html->meta('icon');
				
		return BaseView::renderLayout($content, $layout);
	}
}