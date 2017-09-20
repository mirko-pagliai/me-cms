<?php
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
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;

/**
 * Base view class.
 * This class contains common methods, so you should not use it directly.
 * Instead, use `AppView` or `AdminView`.
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
     * Constructor
     * @param \Cake\Network\Request|null $request Request instance
     * @param \Cake\Network\Response|null $response Response instance
     * @param \Cake\Event\EventManager|null $eventManager Event manager instance
     * @param array $viewOptions View options. See View::$_passedVars for list of
     *   options which get set as class properties
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $viewOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $viewOptions);

        //Sets the theme from configuration
        if (getConfig('default.theme')) {
            $this->setTheme(getConfig('default.theme'));
        }
    }

    /**
     * Gets the title for layout
     * @return string Title
     * @uses $titleForLayout
     */
    protected function getTitleForLayout()
    {
        if (!empty($this->titleForLayout)) {
            return $this->titleForLayout;
        }

        //Gets the main title setted by the configuration
        $title = getConfigOrFail('main.title');

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
        $this->loadHelper('Html', ['className' => ME_TOOLS . '.Html']);
        $this->loadHelper(ME_TOOLS . '.Dropdown');
        $this->loadHelper(ME_TOOLS . '.Form');
        $this->loadHelper(ME_TOOLS . '.Library');
        $this->loadHelper(ME_TOOLS . '.Paginator');
        $this->loadHelper(ASSETS . '.Asset');
        $this->loadHelper(THUMBER . '.Thumb');
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
     * @uses getTitleForLayout()
     */
    public function renderLayout($content, $layout = null)
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
