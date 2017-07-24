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

        return collection($methods)->map(function ($method) use ($class) {
            return am(compact('class'), ['name' => $method]);
        })->toList();
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
