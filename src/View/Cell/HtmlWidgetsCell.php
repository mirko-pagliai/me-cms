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
 * @since       2.15.0
 */

namespace MeCms\View\Cell;

use Cake\View\Cell;

/**
 * HtmlWidgets cell
 */
class HtmlWidgetsCell extends Cell
{
    /**
     * Display method. It only renders a template file
     * @param string $template Template name
     * @return void
     */
    public function display($template)
    {
        $this->viewBuilder()->setTemplate($template);
    }
}
