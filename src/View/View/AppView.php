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

use App\View\AppView as BaseView;

/**
 * Application view class
 */
class AppView extends BaseView {
	/**
	 * Gets the title for layou
	 * @return string Title
	 */
	protected function _getTitleForLayout() {
		//Gets the main title assigned by the configuration
		$title = config('main.title');
		
		//For homepage, it uses only the main title
		if($this->request->isCurrent(['_name' => 'homepage']))
			return $title;
		
		//If exists, it adds the title assigned by the controller
		if(!empty($this->viewVars['title']))
			$title = sprintf('%s - %s', $this->viewVars['title'], $title);
		//Else, if exists, it adds the title assigned by the current view
		elseif(!empty($this->fetch('title')))
			$title = sprintf('%s - %s', $this->fetch('title'), $title);
		
		return $title;
	}

	/**
     * Initialization hook method
     * @return void
	 * @see http://api.cakephp.org/3.0/class-Cake.View.View.html#_initialize
	 */
    public function initialize() {
		//Loads helpers
		$this->loadHelper('Html', ['className' => 'MeTools.Html']);
		$this->loadHelper('MeTools.Dropdown');
		$this->loadHelper('MeTools.Form');
		$this->loadHelper('MeTools.Layout');
		$this->loadHelper('MeTools.Library');
		$this->loadHelper('MeTools.Thumb');
		$this->loadHelper('MeTools.Paginator');
		
		//Loads the Recaptcha helper
		if(config('security.recaptcha'))
			$this->loadHelper('MeTools.Recaptcha');
    }
	
	/**
	 * Renders view for given view file and layout
	 * @param string|NULL $view Name of view file to use
	 * @param string|NULL $layout Layout to use
	 * @return string|NULL Rendered content or NULL if content already rendered and returned earlier
	 * @see http://api.cakephp.org/3.0/class-Cake.View.View.html#_render
     * @throws Cake\Core\Exception\Exception
	 */
	public function render($view = NULL, $layout = NULL) {
		if($this->layout === 'default')
			$this->layout = 'frontend';
		
		return parent::render($view, $layout);
	}
	
	/**
	 * Renders a layout. Returns output from _render(). Returns false on error. Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.0/source-class-Cake.View.View.html#477-513
     * @throws Cake\Core\Exception\Exception
	 * @uses _getTitleForLayout()
	 */
	public function renderLayout($content, $layout = NULL) {
		//Assigns the title for layout
		$this->assign('title', $this->_getTitleForLayout());
				
		return parent::renderLayout($content, $layout);
	}
	
	/**
	 * Returns all widgets, reading from configuration
	 * @return string Html code
	 * @uses widget()
	 */
	public function allWidgets() {
		$widgets = config('frontend.widgets.general');
		
		if($this->request->isCurrent(['_name' => 'homepage']) && !empty(config('frontend.widgets.homepage')))
			$widgets = config('frontend.widgets.homepage');
		
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
	 * @uses Cake\View\Cell::cell()
	 */
	public function widget($name, array $arguments = [], array $options = []) {
		return $this->cell($name, $arguments, $options);
	}
}