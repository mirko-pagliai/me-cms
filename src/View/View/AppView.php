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
use Cake\Routing\Router;

/**
 * Application view class
 */
class AppView extends BaseView {
	/**
	 * It will contain the page title.
	 * To get the title, you should use the `_getTitleForLayout()` method
	 * @see _getTitleForLayout()
	 * @var string
	 */
	protected $title;
	
	/**
	 * Adds Facebook tags
	 * @uses MeTools\View\Helper\HtmlHelper::meta()
	 * @uses _getTitleForLayout()
	 */
	protected function _addFacebookTags() {
		$this->Html->meta(['content' => $this->_getTitleForLayout(), 'property' => 'og:title']);
		$this->Html->meta(['content' => Router::url(NULL, TRUE), 'property' => 'og:url']);
		
		//Adds the app ID
		if(config('frontend.facebook_app_id'))
			$this->Html->meta(['content' => config('frontend.facebook_app_id'), 'property' => 'fb:app_id']);
	}
	
	/**
	 * Gets the title for layou
	 * @return string Title
	 * @uses title
	 * @uses viewVars
	 */
	protected function _getTitleForLayout() {
		if(!empty($this->title))
			return $this->title;
		
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
		
		return $this->title = $title;
	}

	/**
     * Initialization hook method
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_initialize
	 * @uses App\View\AppView::initialize()
	 */
    public function initialize() {
		parent::initialize();
		
		//Loads helpers
		$this->loadHelper('Html', ['className' => 'MeTools.Html']);
		$this->loadHelper('MeTools.BBCode');
		$this->loadHelper('MeTools.Dropdown');
		$this->loadHelper('MeTools.Form');
		$this->loadHelper('MeTools.Asset');
		$this->loadHelper('MeTools.Library');
		$this->loadHelper('MeTools.Paginator');
		$this->loadHelper('MeTools.Recaptcha');
		$this->loadHelper('Thumbs.Thumb');
		$this->loadHelper('MeCms.Auth');
		$this->loadHelper('MeCms.Widget');
    }
	
	/**
	 * Renders view for given view file and layout
	 * @param string|NULL $view Name of view file to use
	 * @param string|NULL $layout Layout to use
	 * @return string|NULL Rendered content or NULL if content already rendered and returned earlier
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_render
	 * @uses App\View\AppView::render()
	 * @uses layout
	 * @uses theme
	 */
	public function render($view = NULL, $layout = NULL) {
		//Sets the theme
		if(config('frontend.theme') && !$this->theme)
			$this->theme = config('frontend.theme');
		
		//Sets the layout
		if($this->layout === 'default')
			$this->layout = config('frontend.layout');
		
		return parent::render($view, $layout);
	}
	
	/**
	 * Renders a layout. Returns output from _render(). Returns false on error. Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.2/source-class-Cake.View.View.html#477-513
	 * @uses App\View\AppView::renderLayout()
	 * @uses MeTools\View\Helper\HtmlHelper::meta()
	 * @uses MeTools\View\Helper\LibraryHelper::analytics()
	 * @uses MeTools\View\Helper\LibraryHelper::shareaholic()
	 * @uses _getTitleForLayout()
	 * @uses _addFacebookTags()
	 */
	public function renderLayout($content, $layout = NULL) {
		//Assigns the title for layout
		$this->assign('title', $this->_getTitleForLayout());
		
		//Adds the favicon
		if(is_readable(WWW_ROOT.'favicon.ico'))
			$this->Html->meta('icon');
		
		//Adds the "theme color" (the toolbar color for some mobile browser)
		if(config('frontend.toolbar_color'))
			$this->Html->meta('theme-color', config('frontend.toolbar_color'));
		
		//Adds the meta tag for RSS posts
		if(config('frontend.rss_meta'))
			$this->Html->meta(__d('me_cms', 'Latest posts'), '/posts/rss', ['type' => 'rss']);
		
		//Adds Google Analytics
		if(config('frontend.analytics'))
			echo $this->Library->analytics(config('frontend.analytics'));

		//Adds Shareaholic
		if(config('shareaholic.site_id'))
			echo $this->Library->shareaholic(config('shareaholic.site_id'));
		
		//Adds Facebook's tags
		$this->_addFacebookTags();
		
		return parent::renderLayout($content, $layout);
	}
}