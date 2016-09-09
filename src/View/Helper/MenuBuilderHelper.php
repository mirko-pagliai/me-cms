<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\View\Helper;

use Cake\Network\Exception\InternalErrorException;
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
        'Html' => ['className' => 'MeTools.Html'],
        'MeTools.Dropdown',
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

        //Loads the helper
        $helper = $this->_View->loadHelper(
            sprintf('%s.Menu', $plugin),
            ['className' => sprintf('%s.Menu', $plugin)]
        );

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
     * @return array|null
     * @throws InternalErrorException
     */
    public function getMenuMethods($plugin)
    {
        //Sets the class name (`$PLUGIN\View\Helper\MenuHelper`)
        $class = sprintf('\%s\View\Helper\MenuHelper', $plugin);

        if (!class_exists($class)) {
            throw new InternalErrorException(
                __d('Class {0} doesn\'t exists', $class)
            );
        }

        //Gets all methods from the class
        $methods = get_class_methods($class);

        if (empty($methods)) {
            return null;
        }

        //Each class menu class extends `Cake\View\Helper`. So it calculates
        //  the difference between the methods of the two classes
        $methods = array_diff($methods, get_class_methods('Cake\View\Helper'));

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
            $collapseName = sprintf(
                'collapse-%s',
                strtolower(Inflector::slug($menu['title']))
            );

            return $this->Html->div('panel', implode(PHP_EOL, [
                $this->Html->link(
                    $menu['title'],
                    sprintf('#%s', $collapseName),
                    am($menu['titleOptions'], [
                        'aria-controls' => $collapseName,
                        'aria-expanded' => 'false',
                        'class' => 'collapsed',
                        'data-toggle' => 'collapse',
                    ])
                ),
                $this->Html->div(
                    'collapse',
                    implode(PHP_EOL, $menu['menu']),
                    ['id' => $collapseName]
                )
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
            return $this->Dropdown->menu(
                $menu['title'],
                $menu['menu'],
                $menu['titleOptions']
            );
        }, $menus);
    }
}
