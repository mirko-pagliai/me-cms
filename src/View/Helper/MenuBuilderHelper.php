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
use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * MenuBuilder Helper
 *
 * An helper to generate the admin menus.
 */
class MenuBuilderHelper extends Helper
{
    /**
     * Helpers
     * @var array
     */
    public $helpers = [
        'MeTools.Dropdown',
        'Html' => ['className' => 'MeTools.Html'],
    ];

    /**
     * Internal method to build links, converting them from array of parameters
     *  (title and url) to html string
     * @param array $links Array of links parameters
     * @param array $options Array of options and HTML attributes. These will be
     *  applied to all generated links
     * @return array
     */
    protected function buildLinks(array $links, array $options = []): array
    {
        return array_map(function ($link) use ($options) {
            [$title, $url] = $link;

            return $this->Html->link($title, $url, $options);
        }, $links);
    }

    /**
     * Generates all menus for a plugin
     * @param string $plugin Plugin name
     * @return array Menus
     */
    public function generate(string $plugin): array
    {
        //Gets all valid methods from `$PLUGIN\View\Helper\MenuHelper`
        $methods = get_child_methods(sprintf('\%s\View\Helper\MenuHelper', $plugin));
        $methods = $methods ? array_values(array_filter($methods, function ($method) {
            return !string_starts_with($method, '_');
        })) : [];

        if (empty($methods)) {
            return [];
        }

        $className = sprintf('%s.Menu', $plugin);
        $helper = $this->getView()->loadHelper($className, compact('className'));

        //Calls dynamically each method
        $menus = [];
        foreach ($methods as $method) {
            $args = call_user_func([$helper, $method]);
            if (!$args) {
                continue;
            }

            is_true_or_fail(
                count($args) >= 3,
                __d('me_cms', 'Method `{0}::{1}()` returned only {2} values', get_class($helper), $method, count($args)),
                BadMethodCallException::class
            );

            [$links, $title, $titleOptions, $handledControllers] = $args + [[], [], [], []];
            $menus[sprintf('%s.%s', $plugin, $method)] = compact('links', 'title', 'titleOptions', 'handledControllers');
        }

        return $menus;
    }

    /**
     * Renders a menu as "collapse"
     * @param string $plugin Plugin name
     * @param string|null $idContainer Container ID
     * @return string
     * @uses buildLinks()
     * @uses generate()
     */
    public function renderAsCollapse(string $plugin, ?string $idContainer = null): string
    {
        $controller = $this->getView()->getRequest()->getParam('controller');

        return implode(PHP_EOL, array_map(function (array $menu) use ($controller, $idContainer) {
            $collapseName = 'collapse-' . strtolower(Text::slug($menu['title']));
            $titleOptions = [
                'aria-controls' => $collapseName,
                'aria-expanded' => 'false',
                'class' => 'collapsed',
                'data-toggle' => 'collapse',
            ] + $menu['titleOptions'];
            $divOptions = [
                'class' => 'collapse',
                'id' => $collapseName,
            ];

            if ($idContainer) {
                $divOptions['data-parent'] = '#' . $idContainer;
            }

            //If the current controller is handled by this menu, opens the menu
            if (in_array($controller, $menu['handledControllers'])) {
                $titleOptions['aria-expanded'] = 'true';
                unset($titleOptions['class']);
                $divOptions['class'] .= ' show';
            }

            $title = $this->Html->link($menu['title'], '#' . $collapseName, $titleOptions);
            $links = $this->Html->div(null, implode(PHP_EOL, $this->buildLinks($menu['links'])), $divOptions);

            return $this->Html->div('card', $title . PHP_EOL . $links);
        }, $this->generate($plugin)));
    }

    /**
     * Renders a menu as "dropdown"
     * @param string $plugin Plugin name
     * @param array $titleOptions HTML attributes of the title
     * @return array
     * @uses buildLinks()
     * @uses generate()
     */
    public function renderAsDropdown(string $plugin, array $titleOptions = []): array
    {
        return array_map(function (array $menu) use ($titleOptions) {
            return $this->Dropdown->menu(
                $menu['title'],
                $this->buildLinks($menu['links'], ['class' => 'dropdown-item']),
                optionsParser($menu['titleOptions'], $titleOptions)->toArray()
            );
        }, $this->generate($plugin));
    }
}
