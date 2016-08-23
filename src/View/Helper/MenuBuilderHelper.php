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
     * Internal function to render a menu as "collapse"
     * @param string $title The content to be wrapped by <a> tags
     * @param array $options Array of options and HTML attributes
     * @param array $menu Menu
     * @return string Html code
     * @uses MeTools\View\Helper\HtmlHelper::div()
     * @uses MeTools\View\Helper\HtmlHelper::link()
     */
    protected function renderAsCollapse($title, array $options = [], $menu)
    {
        //Sets the collapse name
        $collapseName = sprintf('collapse-%s', strtolower($title));

        return $this->Html->div('panel', implode(PHP_EOL, [
            $this->Html->link(
                $title,
                sprintf('#%s', $collapseName),
                am($options, [
                    'aria-controls' => $collapseName,
                    'aria-expanded' => 'false',
                    'class' => 'collapsed',
                    'data-toggle' => 'collapse',
                ])
            ),
            $this->Html->div(
                'collapse',
                implode(PHP_EOL, $menu),
                ['id' => $collapseName]
            )
        ]));
    }

    /**
     * Internal function to render a menu as "dropdown"
     * @param string $title The content to be wrapped by <a> tags
     * @param array $options Array of options and HTML attributes
     * @param array $menu Menu
     * @return string Html code
     * @uses MeTools\View\Helper\DropdownHelper::menu()
     */
    protected function renderAsDropdown($title, array $options = [], $menu)
    {
        return $this->Html->li($this->Dropdown->menu($title, $options, $menu));
    }

    /**
     * Internal function to render a menu as "list"
     * @param string $title The content to be wrapped by <a> tags
     * @param array $options Array of options and HTML attributes
     * @param array $menu Menu
     * @return string Html code
     * @uses MeTools\View\Helper\HtmlHelper::ul()
     */
    protected function renderAsList($title, array $options = [], $menu)
    {
        return $this->Html->ul($menu);
    }

    /**
     * Generates all menus for a plugin
     * @param string $plugin Plugin name
     * @param string $type Type (`collapse`, `dropdown` or `list`)
     * @return string|null Html code
     * @uses renderAsCollapse()
     * @uses renderAsDropdown()
     * @uses renderAsList()
     */
    public function generate($plugin, $type = 'collapse')
    {
        //Gets all methods from `$PLUGIN\View\Helper\MenuHelper` class
        $methods = get_class_methods(sprintf('\%s\View\Helper\MenuHelper', $plugin));

        if (empty($methods)) {
            return null;
        }

        //Because each class is an extension of `\Cake\View\Helper`,
        //  it calculates the difference between the methods of the two classes
        $methods = array_diff($methods, get_class_methods('\Cake\View\Helper'));

        //Sets the helper name
        $helper = sprintf('%sMenu', $plugin);

        //Loads the helper
        $this->{$helper} = $this->_View->loadHelper($helper, ['className' => sprintf('%s.Menu', $plugin)]);

        $menus = [];

        foreach ($methods as $method) {
            //Calls dynamically the method from the menu helper
            list($menu, $title, $options) = $this->{$helper}->{$method}();

            if (empty($menu) || empty($title) || empty($options)) {
                continue;
            }

            //Calls dynamically the internal render method
            $menus[] = $this->{sprintf('renderAs%s', ucfirst($type))}($title, $options, $menu);
        }

        return implode(PHP_EOL, $menus);
    }
}
