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

namespace MeCms\View\View\Admin;

use MeCms\View\View;

/**
 * Application view class for admin views
 * @property \MeCms\View\Helper\MenuBuilderHelper $MenuBuilder
 */
class AppView extends View
{
    /**
     * The name of the layout file to render the template inside of
     * @var string
     */
    public $layout = 'MeCms.admin';

    /**
     * Initialization hook method
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadHelper('MeCms.MenuBuilder');
    }

    /**
     * Renders view for given template file and layout
     * @param string|null $template Name of template file to use
     * @param string|false|null $layout Layout to use. False to disable
     * @return string Rendered content
     */
    public function render(?string $template = null, $layout = null): string
    {
        //Sets some view vars
        $this->set('priorities', [
            '1' => sprintf('1 - %s', __d('me_cms', 'Very low')),
            '2' => sprintf('2 - %s', __d('me_cms', 'Low')),
            '3' => sprintf('3 - %s', __d('me_cms', 'Normal')),
            '4' => sprintf('4 - %s', __d('me_cms', 'High')),
            '5' => sprintf('5 - %s', __d('me_cms', 'Very high')),
        ]);

        return parent::render($template, $layout);
    }
}
