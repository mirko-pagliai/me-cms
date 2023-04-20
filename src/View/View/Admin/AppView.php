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

use Cake\Core\Configure;
use MeCms\Core\Plugin;
use MeCms\View\Helper\AbstractMenuHelper;
use MeCms\View\View;
use Tools\Exceptionist;

/**
 * Application view class for admin views
 * @property \MeCms\View\Helper\PriorityBadgeHelper $PriorityBadge
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

        $this->loadHelper('MeCms.PriorityBadge');
    }

    /**
     * Gets all "menu helpers", as loaded helpers
     * @return \MeCms\View\Helper\AbstractMenuHelper[]
     * @since 2.32.0
     * @throws \ReflectionException|\Exception
     * @throws \Tools\Exception\ObjectWrongInstanceException
     */
    public function getAllMenuHelpers(): array
    {
        $plugins = array_filter(Plugin::extensions(), fn(string $plugin): bool => Configure::check($plugin . '.MenuHelpers'));

        //Merges the "menu helpers" from MeCms with the "menu helpers" from any extension plugin
        $classes = array_merge(
            (array)Configure::readOrFail('MeCms.MenuHelpers'),
            ...array_map(fn(string $plugin): array => (array)Configure::read($plugin . '.MenuHelpers'), $plugins)
        );

        return array_map(function (string $className): AbstractMenuHelper {
            /** @var class-string<\MeCms\View\Helper\AbstractMenuHelper> $className */
            Exceptionist::isInstanceOf($className, AbstractMenuHelper::class);

            /** @var \MeCms\View\Helper\AbstractMenuHelper $Helper */
            $Helper = $this->helpers()->load(get_class_short_name($className), compact('className'));

            return $Helper;
        }, $classes);
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
