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
		elseif($this->fetch('title'))
			$title = sprintf('%s - %s', $this->fetch('title'), $title);
		
		return $title;
	}

	/**
     * Initialization hook method
	 * @see http://api.cakephp.org/3.0/class-Cake.View.View.html#_initialize
	 * @uses App\View\AppView::initialize()
	 */
    public function initialize() {
		parent::initialize();
		
		//Loads helpers
		$this->loadHelper('Html', ['className' => 'MeTools.Html']);
		$this->loadHelper('MeTools.Dropdown');
		$this->loadHelper('MeTools.Form');
		$this->loadHelper('MeTools.Layout');
		$this->loadHelper('MeTools.Library');
		$this->loadHelper('MeTools.Thumb');
		$this->loadHelper('MeTools.Paginator');
		$this->loadHelper('MeCms.Auth');
		$this->loadHelper('MeTools.Recaptcha');
    }
	
	/**
	 * Renders view for given view file and layout
	 * @param string|NULL $view Name of view file to use
	 * @param string|NULL $layout Layout to use
	 * @return string|NULL Rendered content or NULL if content already rendered and returned earlier
	 * @see http://api.cakephp.org/3.0/class-Cake.View.View.html#_render
     * @throws Cake\Core\Exception\Exception
	 * @uses MeTools\Core\Plugin::path()
	 * @uses layout
	 * @uses theme
	 */
	public function render($view = NULL, $layout = NULL) {
		//Enables the theme
		if(config('frontend.theme') && !$this->theme)
			$this->theme = config('frontend.theme');
		
		if($this->layout === 'default') {
			//Sets the layout relative path
			$path = 'Template'.DS.'Layout'.($this->request->param('_ext') ? DS.$this->request->param('_ext').DS : DS).'frontend.ctp';
		
			//It first tries to get the layout from the theme
			if($this->theme && is_readable(\MeTools\Core\Plugin::path($this->theme, 'src'.DS.$path)))
				$this->layout = sprintf('%s.frontend', $this->theme);
			//Otherwise, it tries to get the layout from the application
			elseif(is_readable(APP.$path))
				$this->layout = 'frontend';
			//Otherwise, it gets the layout from the plugin
			else
				$this->layout = 'MeCms.frontend';
		}
		
		return parent::render($view, $layout);
	}
	
	/**
	 * Renders a layout. Returns output from _render(). Returns false on error. Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.0/source-class-Cake.View.View.html#477-513
     * @throws Cake\Core\Exception\Exception
	 * @uses MeTools\View\Helper\HtmlHelper::meta()
	 * @uses _getTitleForLayout()
	 */
	public function renderLayout($content, $layout = NULL) {
		//Assigns the title for layout
		$this->assign('title', $this->_getTitleForLayout());
		
		//Automatically adds the meta tag for RSS posts
		if(config('frontend.rss_meta'))
			$this->Html->meta(__d('me_cms', 'Latest posts'), '/posts/rss', ['type' => 'rss']);
				
		return parent::renderLayout($content, $layout);
	}
	
	/**
	 * Returns all widgets, reading from configuration
	 * @return string Html code
	 * @uses MeTools\Network\Request::isCurrent()
	 * @uses widget()
	 */
	public function allWidgets() {
		$widgets = config('frontend.widgets.general');
		
		if($this->request->isCurrent(['_name' => 'homepage']) && config('frontend.widgets.homepage'))
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