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
use MeTools\View\OptionsParserTrait;

/**
 * MenuBuilder Helper
 *
 * An helper to generate the admin menus.
 */
class MenuBuilderHelper extends Helper
{
    use OptionsParserTrait;

    /**
     * Helpers
     * @var array
     */
    public $helpers = [
        'Html' => ['className' => ME_TOOLS . '.Html'],
        ME_TOOLS . '.Dropdown',
    ];

    /**
     * Internal method to build links, converting them from array to html
     * @param array $links Array of links parameters
     * @param array $linksOptions Array of options and HTML attributes
     * @return array
     */
    protected function buildLinks($links, array $linksOptions = [])
    {
        return collection($links)
            ->map(function ($link) use ($linksOptions) {
                return $this->Html->link($link[0], $link[1], $linksOptions);
            })
            ->toArray();
    }

    /**
     * Internal method to get all menu methods names from a plugin
     * @param string $plugin Plugin name
     * @return array
     */
    protected function getMenuMethods($plugin)
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
        $menus = collection($this->generate($plugin))
            ->map(function ($menu) {
                //Sets the collapse name
                $collapseName = 'collapse-' . strtolower(Inflector::slug($menu['title']));

                $mainLink = $this->Html->link(
                    $menu['title'],
                    sprintf('#%s', $collapseName),
                    array_merge($menu['titleOptions'], [
                        'aria-controls' => $collapseName,
                        'aria-expanded' => 'false',
                        'class' => 'collapsed',
                        'data-toggle' => 'collapse',
                    ])
                );

                return $this->Html->div(
                    'card',
                    $mainLink . $this->Html->div('collapse', $this->buildLinks($menu['links']), ['id' => $collapseName])
                );
            });

        return implode(PHP_EOL, $menus->toArray());
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
        return collection($this->generate($plugin))
            ->map(function ($menu) use ($titleOptions) {
                $titleOptions = array_merge($menu['titleOptions'], $titleOptions);
                echo $this->Dropdown->start($menu['title'], $titleOptions);
                echo implode(PHP_EOL, $this->buildLinks($menu['links'], ['class' => 'dropdown-item']));

                return $this->Dropdown->end();
            })
            ->toArray();
    }
}
