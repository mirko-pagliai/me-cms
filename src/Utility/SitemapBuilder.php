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
namespace MeCms\Utility;

use Cake\Routing\Router;
use Cake\Utility\Xml;
use MeCms\Core\Plugin;

/**
 * An utility to generate a sitemap
 */
class SitemapBuilder
{
    /**
     * Internal method to get methods from `Sitemap` classes
     * @param string $plugin Plugin
     * @return array Array with classes and methods names
     */
    protected static function _getMethods($plugin)
    {
        //Sets the class name
        $class = sprintf('\%s\Utility\Sitemap', $plugin);

        //Gets all methods from the `Sitemap` class of the plugin
        $methods = getChildMethods($class);

        if (empty($methods)) {
            return [];
        }

        return array_map(function ($method) use ($class) {
            return ['class' => $class, 'name' => $method];
        }, $methods);
    }

    /**
     * Parses url
     * @param string|array|null $url Url
     * @param array $options Options, for example `lastmod` or `priority`
     * @return array
     * @see Cake\Routing\Router::url()
     */
    protected static function parse($url, array $options = [])
    {
        if (!empty($options['lastmod']) && !is_string($options['lastmod'])) {
            $options['lastmod'] = $options['lastmod']->format('c');
        }

        if (empty($options['priority'])) {
            $options['priority'] = '0.5';
        }

        return am(['loc' => Router::url($url, true)], $options);
    }

    /**
     * Generate the sitemap.
     *
     * For each plugin, calls dynamically all methods from the `Sitemap` class.
     * Each method must be return an array or urls to add to the sitemap.
     * @return string
     * @see MeCms\Utility\Sitemap
     * @uses MeCms\Core\Plugin::all()
     * @uses _getMethods()
     * @uses parse()
     */
    public static function generate()
    {
        //Adds the homepage
        $url[] = self::parse('/');

        foreach (Plugin::all() as $plugin) {
            //Gets all methods from `Sitemap` class of the plugin
            $methods = self::_getMethods($plugin);

            //Calls each method
            foreach ($methods as $method) {
                $url = am($url, call_user_func([$method['class'], $method['name']]));
            }
        }

        $xml = Xml::fromArray(['urlset' => [
            'xmlns:' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'url' => $url,
        ]], ['pretty' => true]);

        return trim($xml->asXML());
    }
}
