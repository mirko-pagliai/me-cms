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
use Cake\Utility\Text;
use Cake\View\Helper;
use Tools\Exceptionist;

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
        'Dropdown' => ['className' => 'MeTools.BootstrapDropdown'],
        'Html' => ['className' => 'MeTools.BootstrapHtml'],
    ];

    /**
     * Internal method to build links, converting them from array of parameters
     *  (title and url) to html string
     * @param array $links Array of links parameters
     * @param array $options Array of options and HTML attributes. These will be
     *  applied to all generated links
     * @return array<string> Array of links as html string
     */
    protected function buildLinks(array $links, array $options = []): array
    {
        return array_map(fn(array $link): string => $this->Html->link($link[0], $link[1], $options), $links);
    }

    /**
     * Gets all valid methods from the `MenuHelper` provided by a plugin
     * @param string $plugin Plugin name
     * @return array<string>
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

                Exceptionist::isTrue(count($args) >= 3, __d('me_cms', 'Method `{0}::{1}()` returned only {2} values', $className, $method, count($args)), BadMethodCallException::class);

                [$links, $title, $titleOptions, $handledControllers] = $args + [[], [], [], []];
                $menus[$plugin . '.' . $method] = compact('links', 'title', 'titleOptions', 'handledControllers');
            }
        }

        return $menus ?? [];
    }

    /**
     * Renders a menu as "collapse".
     *
     * The menu can be previously generated with the `generate()` method.
     * @param array $menu The menu
     * @param string|null $idContainer Container ID
     * @return string
     */
    public function renderAsCollapse(array $menu, ?string $idContainer = null): string
    {
        $collapseName = 'collapse-' . strtolower(Text::slug($menu['title']));
        $titleOptions = [
            'aria-controls' => $collapseName,
            'aria-expanded' => 'false',
            'class' => 'collapsed',
            'data-toggle' => 'collapse',
        ] + $menu['titleOptions'];
        $divOptions = ['class' => 'collapse', 'id' => $collapseName];

        if ($idContainer) {
            $divOptions['data-parent'] = '#' . $idContainer;
        }

        //If the current controller is handled by this menu, marks the menu as open
        if (in_array($this->getView()->getRequest()->getParam('controller'), $menu['handledControllers'])) {
            $titleOptions['aria-expanded'] = 'true';
            unset($titleOptions['class']);
            $divOptions['class'] .= ' show';
        }

        $title = $this->Html->link($menu['title'], '#' . $collapseName, $titleOptions);
        $links = $this->Html->div(null, implode(PHP_EOL, $this->buildLinks($menu['links'])), $divOptions);

        return $this->Html->div('card', $title . PHP_EOL . $links);
    }
}
