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
namespace MeCms\View\View;

use MeCms\View\View;

/**
 * Application view class for admin views
 */
class AdminView extends View
{
    /**
     * The name of the layout file to render the template inside of
     * @var string
     */
    public $layout = 'MeCms.admin';

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
        $this->loadHelper('MeCms.MenuBuilder');
        $this->loadHelper('Gourmet/CommonMark.CommonMark');
    }

    /**
     * Renders view for given template file and layout
     * @param string|null $view Name of view file to use
     * @param string|null $layout Layout to use
     * @return Rendered content or null if content already rendered and
     *  returned earlier
     * @see http://api.cakephp.org/3.4/class-Cake.View.View.html#_render
     */
    public function render($view = null, $layout = null)
    {
        //Sets some view vars
        $this->set('priorities', [
            '1' => sprintf('1 - %s', __d('me_cms', 'Very low')),
            '2' => sprintf('2 - %s', __d('me_cms', 'Low')),
            '3' => sprintf('3 - %s', __d('me_cms', 'Normal')),
            '4' => sprintf('4 - %s', __d('me_cms', 'High')),
            '5' => sprintf('5 - %s', __d('me_cms', 'Very high'))
        ]);

        return parent::render($view, $layout);
    }
}
