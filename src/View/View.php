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
namespace MeCms\View;

use App\View\AppView;

/**
 * Base view class.
 * This class contains common methods, so you should not use it directly.
 * Instead, use `AppView` or `AdminView`.
 */
class View extends AppView
{
    /**
     * Title for layout.
     * To get the title, you should use the `_getTitleForLayout()` method
     * @see _getTitleForLayout()
     * @var string
     */
    protected $titleForLayout;

    /**
     * Constructor
     * @param \Cake\Network\Request|null $request Request instance
     * @param \Cake\Network\Response|null $response Response instance
     * @param \Cake\Event\EventManager|null $eventManager Event manager instance
     * @param array $viewOptions View options. See View::$_passedVars for list of
     *   options which get set as class properties
     */
    public function __construct(
        \Cake\Network\Request $request = null,
        \Cake\Network\Response $response = null,
        \Cake\Event\EventManager $eventManager = null,
        array $viewOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $viewOptions);

        //Sets the theme from configuration
        if (config('default.theme')) {
            $this->theme(config('default.theme'));
        }
    }

    /**
     * Gets the title for layout
     * @return string Title
     * @uses $titleForLayout
     */
    protected function _getTitleForLayout()
    {
        if (!empty($this->titleForLayout)) {
            return $this->titleForLayout;
        }

        //Gets the main title setted by the configuration
        $title = config('main.title');

        //For homepage, it returns only the main title
        if ($this->request->isUrl(['_name' => 'homepage'])) {
            return $title;
        }

        //If exists, it adds the title setted by the controller, as if it has
        //  been set via `$this->View->set()`
        if ($this->get('title')) {
            $title = sprintf('%s - %s', $this->get('title'), $title);
        //Else, if exists, it adds the title setted by the current view, as if
        //  it has been set via `$this->View->Blocks->set()`
        } elseif ($this->fetch('title')) {
            $title = sprintf('%s - %s', $this->fetch('title'), $title);
        }

        return $this->titleForLayout = $title;
    }

    /**
     * Initialization hook method
     * @return void
     * @see http://api.cakephp.org/3.4/class-Cake.View.View.html#_initialize
     * @uses App\View\AppView::initialize()
     */
    public function initialize()
    {
        parent::initialize();

        //Loads helpers
        $this->loadHelper('Html', ['className' => 'MeTools.Html']);
        $this->loadHelper('MeTools.Dropdown');
        $this->loadHelper('MeTools.Form');
        $this->loadHelper('MeTools.Library');
        $this->loadHelper('MeTools.Paginator');
        $this->loadHelper('Assets.Asset');
        $this->loadHelper('Thumber.Thumb');
        $this->loadHelper('WyriHaximus/MinifyHtml.MinifyHtml');
    }

    /**
     * Renders a layout. Returns output from _render(). Returns false on error.
     *  Several variables are created for use in layout
     * @param string $content Content to render in a view, wrapped by the
     *  surrounding layout
     * @param string|null $layout Layout name
     * @return mixed Rendered output, or false on error
     * @see http://api.cakephp.org/3.4/class-Cake.View.View.html#_renderLayout
     * @uses MeTools\View\Helper\HtmlHelper::meta()
     * @uses _getTitleForLayout()
     */
    public function renderLayout($content, $layout = null)
    {
        //Sets the title for layout
        $this->assign('title', $this->_getTitleForLayout());

        //Adds the favicon
        if (is_readable(WWW_ROOT . 'favicon.ico')) {
            $this->Html->meta('icon');
        }

        return parent::renderLayout($content, $layout);
    }
}
