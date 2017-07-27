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
        'Html' => ['className' => METOOLS . '.Html'],
        METOOLS . '.Dropdown',
    ];

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
            list($menu, $title, $titleOptions) = call_user_func([$helper, $method]);

            if (empty($menu) || empty($title)) {
                continue;
            }

            $menus[sprintf('%s.%s', $plugin, $method)] = compact('menu', 'title', 'titleOptions');
        }

        return $menus;
    }

    /**
     * Gets all menu name methods from a plugin
     * @param string $plugin Plugin name
     * @return array
     */
    public function getMenuMethods($plugin)
    {
        //Gets all methods from `$PLUGIN\View\Helper\MenuHelper`
        $methods = getChildMethods(sprintf('\%s\View\Helper\MenuHelper', $plugin));

        if (empty($methods)) {
            return [];
        }

        //Filters invalid name methods
        $methods = preg_grep('/^(?!_).+$/', $methods);

        return array_values($methods);
    }

    /**
     * Renders a menu as "collapse"
     * @param string $plugin Plugin name
     * @return string
     * @uses generate()
     */
    public function renderAsCollapse($plugin)
    {
        //Gets menus
        $menus = $this->generate($plugin);

        $menus = array_map(function ($menu) {
            //Sets the collapse name
            $collapseName = 'collapse-' . strtolower(Inflector::slug($menu['title']));

            return $this->Html->div('panel', implode(PHP_EOL, [
                $this->Html->link($menu['title'], '#' . $collapseName, am($menu['titleOptions'], [
                    'aria-controls' => $collapseName,
                    'aria-expanded' => 'false',
                    'class' => 'collapsed',
                    'data-toggle' => 'collapse',
                ])),
                $this->Html->div('collapse', implode(PHP_EOL, $menu['menu']), ['id' => $collapseName])
            ]));
        }, $menus);

        return implode(PHP_EOL, $menus);
    }

    /**
     * Renders a menu as "dropdown"
     * @param string $plugin Plugin name
     * @return array
     * @uses generate()
     */
    public function renderAsDropdown($plugin)
    {
        //Gets menus
        $menus = $this->generate($plugin);

        return array_map(function ($menu) {
            return $this->Dropdown->menu($menu['title'], $menu['menu'], $menu['titleOptions']);
        }, $menus);
    }
}
