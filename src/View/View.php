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
 * This class contains common methods, so you should not use it directly.
 * Instead, use `AppView` or `AdminView`.
 * @property \MeTools\View\Helper\LibraryHelper $Library
 * @property \MeTools\View\Helper\HtmlHelper $Html
 */
class View extends AppView
{
    /**
     * Title for layout.
     * To get the title, you should use the `getTitleForLayout()` method
     * @see getTitleForLayout()
     * @var string
     */
    protected $titleForLayout;

    /**
     * Gets the title for layout
     * @return string Title
     * @uses $titleForLayout
     */
    protected function getTitleForLayout(): string
    {
        if (!empty($this->titleForLayout)) {
            return $this->titleForLayout;
        }

        //Gets the main title setted by the configuration
        $title = getConfigOrFail('main.title');

        //For homepage, it returns only the main title
        if ($this->getRequest()->isUrl(['_name' => 'homepage'])) {
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
     */
    public function initialize(): void
    {
        parent::initialize();

        //Sets the theme from configuration
        if (getConfig('default.theme')) {
            $this->setTheme(getConfig('default.theme'));
        }

        $this->loadHelper('Html', ['className' => 'MeTools.Html']);
        $this->loadHelper('MeTools.Dropdown');
        $this->loadHelper('MeTools.Form');
        $this->loadHelper('MeTools.Icon');
        $this->loadHelper('MeTools.Library');
        $this->loadHelper('MeTools.Paginator');
        $this->loadHelper('Assets.Asset');
        $this->loadHelper('Thumber/Cake.Thumb');
        $this->loadHelper('MeCms.Auth');
        $this->loadHelper('WyriHaximus/MinifyHtml.MinifyHtml');
    }

    /**
     * Renders a layout. Returns output from _render(). Returns false on error.
     *  Several variables are created for use in layout
     * @param string $content Content to render in a view, wrapped by the
     *  surrounding layout
     * @param string|null $layout Layout name
     * @return string Rendered output
     * @uses \MeTools\View\Helper\HtmlHelper::meta()
     * @uses getTitleForLayout()
     */
    public function renderLayout($content, $layout = null): string
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
