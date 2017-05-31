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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\View\View;

use Cake\Routing\Router;
use MeCms\View\View;

/**
 * Application view class for all views, except the admin views
 */
class AppView extends View
{
    /**
     * Internal property to set the userbar elements
     * @var array
     * @see userbar()
     */
    protected $userbar = [];

    /**
     * Internal method to set some blocks
     * @return void
     * @uses $userbar
     * @uses MeCms\View\View::_getTitleForLayout()
     * @uses MeTools\View\Helper\HtmlHelper::meta()
     * @uses MeTools\View\Helper\LibraryHelper::analytics()
     * @uses MeTools\View\Helper\LibraryHelper::shareaholic()
     */
    protected function _setBlocks()
    {
        //Sets the "theme color" (the toolbar color for some mobile browser)
        if (getConfig('default.toolbar_color')) {
            $this->Html->meta('theme-color', getConfig('default.toolbar_color'));
        }

        //Sets the meta tag for RSS posts
        if (getConfig('default.rss_meta')) {
            $this->Html->meta(__d('me_cms', 'Latest posts'), '/posts/rss', ['type' => 'rss']);
        }

        //Sets scripts for Google Analytics
        if (getConfig('default.analytics')) {
            echo $this->Library->analytics(getConfig('default.analytics'));
        }

        //Sets scripts for Shareaholic
        if (getConfig('shareaholic.site_id')) {
            echo $this->Library->shareaholic(getConfig('shareaholic.site_id'));
        }

        //Sets some Facebook's tags
        $this->Html->meta([
            'content' => $this->_getTitleForLayout(),
            'property' => 'og:title',
        ]);
        $this->Html->meta([
            'content' => Router::url(null, true),
            'property' => 'og:url',
        ]);

        //Sets the app ID for Facebook
        if (getConfig('default.facebook_app_id')) {
            $this->Html->meta([
                'content' => getConfig('default.facebook_app_id'),
                'property' => 'fb:app_id',
            ]);
        }
    }

    /**
     * Initialization hook method
     * @return void
     * @see http://api.cakephp.org/3.4/class-Cake.View.View.html#_initialize
     * @uses MeCms\View\View::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads helpers
        $this->loadHelper('MeTools.BBCode');
        $this->loadHelper('MeTools.Breadcrumbs');
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
     * @see http://api.cakephp.org/3.4/class-Cake.View.View.html#_renderLayout
     * @uses MeCms\View\View::renderLayout()
     * @uses _setBlocks()
     * @uses userbar()
     */
    public function renderLayout($content, $layout = null)
    {
        $this->_setBlocks();

        //Assign the userbar
        $this->assign('userbar', implode(PHP_EOL, array_map(function ($element) {
            return $this->Html->li($element);
        }, $this->userbar())));

        return parent::renderLayout($content, $layout);
    }

    /**
     * Sets one or more userbar contents.
     * @param string|array|null $content Contents. It can be a string or an
     *  array of contents. If `null`, returns an array of current contents
     * @return array|void
     * @uses $userbar
     */
    public function userbar($content = null)
    {
        if ($content === null) {
            return $this->userbar;
        }

        $this->userbar = am($this->userbar, (array)$content);
    }
}
