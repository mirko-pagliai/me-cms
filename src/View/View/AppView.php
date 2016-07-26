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
use MeTools\Core\Plugin;
use Cake\Routing\Router;

/**
 * Application view class for all views, except the admin views
 */
class AppView extends BaseView {
    /**
     * Internal property to set the userbar elements
     * @var array
     * @see userbar()
     */
    protected $userbar = [];
    
    /**
	 * Adds Facebook tags
	 * @uses MeCms\View\View\BaseView::_getTitleForLayout()
	 * @uses MeTools\View\Helper\HtmlHelper::meta()
	 */
	protected function _addFacebookTags() {
		$this->Html->meta([
            'content' => $this->_getTitleForLayout(),
            'property' => 'og:title',
        ]);
		$this->Html->meta([
            'content' => Router::url(NULL, TRUE),
            'property' => 'og:url',
        ]);
		
		//Adds the app ID
		if(config('default.facebook_app_id')) {
			$this->Html->meta([
                'content' => config('default.facebook_app_id'),
                'property' => 'fb:app_id',
            ]);
        }
	}

	/**
     * Initialization hook method
	 * @see http://api.cakephp.org/3.2/class-Cake.View.View.html#_initialize
	 * @uses MeCms\View\View\BaseView::initialize()
	 */
    public function initialize() {
		parent::initialize();
		
		//Loads helpers
		$this->loadHelper('MeTools.Breadcrumb');
		$this->loadHelper('MeTools.BBCode');
		$this->loadHelper('MeTools.Recaptcha');
		$this->loadHelper('MeCms.Widget');
    }
	
	/**
	 * Renders a layout. Returns output from _render(). Returns false on 
     *  error. Several variables are created for use in layout
	 * @param string $content Content to render in a view, wrapped by the 
     *  surrounding layout
	 * @param string|null $layout Layout name
	 * @return mixed Rendered output, or false on error
	 * @see http://api.cakephp.org/3.2/source-class-Cake.View.View.html#477-513
     * @uses MeCms\View\View\BaseView::renderLayout()
     * @uses MeTools\Core\Plugin::path()
	 * @uses MeTools\View\Helper\HtmlHelper::meta()
	 * @uses MeTools\View\Helper\LibraryHelper::analytics()
	 * @uses MeTools\View\Helper\LibraryHelper::shareaholic()
	 * @uses _addFacebookTags()
     * @uses $userbar
	 */
	public function renderLayout($content, $layout = NULL) {
        $path = 'src'.DS.'Template'.DS.'Layout'.DS;
        
        if($this->layoutPath()) {
            $path .= $this->layoutPath().DS;
        }
        
        $path .= $layout.'.ctp';
        
        //Uses the APP layout, if exists
        if(is_readable(ROOT.DS.$path)) {
            $this->plugin = FALSE;
        }
        
		//Sets the theme and uses the theme layout, if exists
		if(config('default.theme') && !$this->theme()) {
			$this->theme(config('default.theme'));
            
            if(is_readable(Plugin::path($this->theme()).$path)) {
                $this->plugin = $this->theme();
            }
        }
        
		//Adds the "theme color" (the toolbar color for some mobile browser)
		if(config('default.toolbar_color')) {
			$this->Html->meta('theme-color', config('default.toolbar_color'));
        }
        
		//Adds the meta tag for RSS posts
		if(config('default.rss_meta')) {
			$this->Html->meta(__d('me_cms', 'Latest posts'), '/posts/rss', ['type' => 'rss']);
        }
        
		//Adds Google Analytics
		if(config('default.analytics')) {
			echo $this->Library->analytics(config('default.analytics'));
        }
        
		//Adds Shareaholic
		if(config('shareaholic.site_id')) {
			echo $this->Library->shareaholic(config('shareaholic.site_id'));
        }
        
		//Adds Facebook's tags
		$this->_addFacebookTags();
		
        //Assign the userbar
        $this->assign('userbar', implode(PHP_EOL, array_map(function($element) {
            return $this->Html->li($element);
        }, $this->userbar)));
        
		return parent::renderLayout($content, $layout);
	}
    
    /**
     * Sets one or more userbar elements
     * @param string|array $element
     * @uses $userbar
     */
    public function userbar($element) {
        $this->userbar = am($this->userbar, (array) $element);
    }
}