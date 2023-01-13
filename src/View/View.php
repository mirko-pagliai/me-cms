<?php
declare(strict_types=1);

/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace MeCms\View;

use App\View\AppView;

/**
 * Base view class.
 *
 * This class contains common methods, so you should not use it directly.
 * Instead, use `AppView` or `AdminView`.
 * @property \Assets\View\Helper\AssetHelper $Asset
 * @property \MeTools\View\Helper\DropdownHelper $Dropdown
 * @property \MeTools\View\Helper\FormHelper $Form
 * @property \MeTools\View\Helper\HtmlHelper $Html
 * @property \MeCms\View\Helper\IdentityHelper $Identity
 * @property \MeTools\View\Helper\IconHelper $Icon
 * @property \MeTools\View\Helper\LibraryHelper $Library
 * @property \MeTools\View\Helper\PaginatorHelper $Paginator
 * @property \Thumber\Cake\View\Helper\ThumbHelper $Thumb
 */
abstract class View extends AppView
{
    /**
     * Title for layout.
     * To get the title, you should use the `getTitleForLayout()` method
     * @var string
     */
    protected string $titleForLayout;

    /**
     * Gets the title for layout
     * @return string Title
     */
    protected function getTitleForLayout(): string
    {
        if (!empty($this->titleForLayout)) {
            return $this->titleForLayout;
        }

        //Gets the main title set by the configuration
        $title = getConfigOrFail('main.title');

        //For homepage, it returns only the main title
        if ($this->getRequest()->is('url', ['_name' => 'homepage'])) {
            return $title;
        }

        //If exists, it adds the title set by the controller, as if it has
        //  been set via `$this->View->set()`
        if ($this->get('title')) {
            $title = sprintf('%s - %s', $this->get('title'), $title);
            //Else, if exists, it adds the title set by the current view, as if
            //  it has been set via `$this->View->Blocks->set()`
        } elseif ($this->fetch('title')) {
            $title = sprintf('%s - %s', $this->fetch('title'), $title);
        }

        return $this->titleForLayout = $title;
    }

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        //Sets the theme from configuration
        if (getConfig('default.theme')) {
            $this->setTheme(getConfig('default.theme'));
        }

        $this->loadHelper('Assets.Asset');
        $this->loadHelper('MeCms.Identity');
        $this->loadHelper('MeTools.Dropdown');
        $this->loadHelper('MeTools.Form');
        $this->loadHelper('MeTools.Html');
        $this->loadHelper('MeTools.Icon');
        $this->loadHelper('MeTools.Library');
        $this->loadHelper('MeTools.Paginator');
        $this->loadHelper('Thumber/Cake.Thumb');
        $this->loadHelper('WyriHaximus/MinifyHtml.MinifyHtml');
    }

    /**
     * Checks if an element exists in app.
     *
     * Unlike `elementExists()` method, it excludes plugins.
     * @param string $name Name of template file in the `templates/element/` folder
     * @return bool
     * @since 2.30.11
     */
    public function elementExistsInApp(string $name): bool
    {
        return (bool)$this->_getElementFileName($name, false);
    }

    /**
     * Renders a layout. Returns output from _render().
     *
     * Several variables are created for use in layout.
     * @param string $content Content to render in a template, wrapped by the surrounding layout
     * @param string|null $layout Layout name
     * @return string Rendered output
     */
    public function renderLayout(string $content, ?string $layout = null): string
    {
        //Sets the title for layout
        $this->assign('title', $this->getTitleForLayout());

        //Adds the favicon
        if (is_readable(WWW_ROOT . 'favicon.ico')) {
            $this->Html->meta('icon');
        }

        return parent::renderLayout($content, $layout);
    }
}
