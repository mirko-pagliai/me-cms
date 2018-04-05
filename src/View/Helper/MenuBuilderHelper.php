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
namespace MeCms\View\Helper;

use Cake\Utility\Inflector;
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
        ME_TOOLS . '.Dropdown',
        'Html' => ['className' => ME_TOOLS . '.Html'],
    ];

    /**
     * Internal method to build links, converting them from array to html
     * @param array $links Array of links parameters
     * @param array $linksOptions Array of options and HTML attributes
     * @return array
     */
    protected function buildLinks($links, array $linksOptions = [])
    {
        return array_map(function ($link) use ($linksOptions) {
            return $this->Html->link($link[0], $link[1], $linksOptions);
        }, $links);
    }

    /**
     * Internal method to get all menu methods names from a plugin
     * @param string $plugin Plugin name
     * @return array
     */
    protected function getMenuMethods($plugin)
    {
        //Gets all methods from `$PLUGIN\View\Helper\MenuHelper`
        $methods = get_child_methods(sprintf('\%s\View\Helper\MenuHelper', $plugin));

        if (empty($methods)) {
            return [];
        }

        //Filters invalid name methods and returns
        return array_values(preg_grep('/^(?!_).+$/', $methods));
    }

    /**
     * Generates all menus for a plugin
     * @param string $plugin Plugin name
     * @return string|null Html code
     * @uses getMenuMethods()
     */
    public function generate($plugin)
    {
        //Gets all menu name methods
        $methods = $this->getMenuMethods($plugin);

        if (empty($methods)) {
            return [];
        }

        $className = sprintf('%s.Menu', $plugin);

        //Loads the helper
        $helper = $this->_View->loadHelper($className, compact('className'));

        $menus = [];

        //Calls dynamically each method
        foreach ($methods as $method) {
            list($links, $title, $titleOptions) = call_user_func([$helper, $method]);

            if (empty($links) || empty($title)) {
                continue;
            }

            $menus[sprintf('%s.%s', $plugin, $method)] = compact('links', 'title', 'titleOptions');
        }

        return $menus;
    }

    /**
     * Renders a menu as "collapse"
     * @param string $plugin Plugin name
     * @return string
     * @uses buildLinks()
     * @uses generate()
     */
    public function renderAsCollapse($plugin)
    {
        return implode(PHP_EOL, array_map(function ($menu) {
            //Sets the collapse name
            $collapseName = 'collapse-' . strtolower(Inflector::slug($menu['title']));
            $titleOptions = optionsParser($menu['titleOptions'], [
                'aria-controls' => $collapseName,
                'aria-expanded' => 'false',
                'class' => 'collapsed',
                'data-toggle' => 'collapse',
            ]);
            $mainLink = $this->Html->link($menu['title'], '#' . $collapseName, $titleOptions->toArray());
            $links = $this->Html->div('collapse', $this->buildLinks($menu['links']), ['id' => $collapseName]);

            return $this->Html->div('card', $mainLink . PHP_EOL . $links);
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
    public function renderAsDropdown($plugin, array $titleOptions = [])
    {
        return array_map(function ($menu) use ($titleOptions) {
            $titleOptions = optionsParser($menu['titleOptions'], $titleOptions);
            $links = $this->buildLinks($menu['links'], ['class' => 'dropdown-item']);

            return $this->Dropdown->menu($menu['title'], $links, $titleOptions->toArray());
        }, $this->generate($plugin));
    }
}
