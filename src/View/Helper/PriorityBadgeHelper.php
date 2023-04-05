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
 * @since       2.31.7
 */

namespace MeCms\View\Helper;

use Cake\View\Helper;

/**
 * PriorityBadge helper
 * @property \MeTools\View\Helper\HtmlHelper $Html
 */
class PriorityBadgeHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = ['MeTools.Html'];

    /**
     * Creates a "priority" badge
     * @param int $priority Priority value
     * @return string Html string
     */
    public function badge(int $priority): string
    {
        switch ($priority) {
            case '1':
                [$priority, $class, $tooltip] = ['1', 'priority-verylow', __d('me_cms', 'Very low')];
                break;
            case '2':
                [$priority, $class, $tooltip] = ['2', 'priority-low', __d('me_cms', 'Low')];
                break;
            case '4':
                [$priority, $class, $tooltip] = ['4', 'priority-high', __d('me_cms', 'High')];
                break;
            case '5':
                [$priority, $class, $tooltip] = ['5', 'priority-veryhigh', __d('me_cms', 'Very high')];
                break;
            default:
                [$priority, $class, $tooltip] = ['3', 'priority-normal', __d('me_cms', 'Normal')];
                break;
        }

        return $this->Html->badge($priority, compact('class', 'tooltip'));
    }
}
