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

namespace MeCms\View\Helper;

use BadMethodCallException;
use Cake\Core\App;
use Cake\View\Helper;

/**
 * MenuBuilder Helper.
 *
 * An helper to generate the admin menus.
 * @property \MeTools\View\Helper\DropdownHelper $Dropdown
 * @property \MeTools\View\Helper\HtmlHelper $Html
 */
class MenuBuilderHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = [
        'MeTools.Dropdown',
        'MeTools.Html',
    ];

    /**
     * Gets all valid methods from the `MenuHelper` provided by a plugin
     * @param string $plugin Plugin name
     * @return array<string>
     * @throws \ErrorException
     * @since 2.30.0
     */
    public function getMethods(string $plugin): array
    {
        $class = App::className($plugin . '.MenuHelper', 'View/Helper');

        return array_clean($class ? get_child_methods($class) : [], fn(string $method): bool => !str_starts_with($method, '_'));
    }

    /**
     * Generates all menus for a plugin
     * @param string $plugin Plugin name
     * @return array<string, array> Menus
     * @throws \ErrorException
     */
    public function generate(string $plugin): array
    {
        $className = App::className($plugin . '.MenuHelper', 'View/Helper');
        if (!$className) {
            return [];
        }
        $Helper = $this->getView()->loadHelper($plugin . '.Menu', compact('className'));

        //Calls dynamically each method
        foreach ($this->getMethods($plugin) as $method) {
            $callable = [$Helper, $method];
            if (is_callable($callable)) {
                $args = call_user_func($callable);
                if (!$args) {
                    continue;
                }

                if (count($args) < 3) {
                    throw new BadMethodCallException(__d('me_cms', 'Method `{0}::{1}()` returned only {2} values', $className, $method, count($args)));
                }

                [$links, $title, $titleOptions, $handledControllers] = $args + [[], [], [], []];
                $menus[$plugin . '.' . $method] = compact('links', 'title', 'titleOptions', 'handledControllers');
            }
        }

        return $menus ?? [];
    }
}
