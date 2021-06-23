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

namespace MeCms\Utility\Sitemap;

use Cake\Collection\CollectionInterface;
use Cake\Routing\Router;
use Cake\Utility\Xml;
use MeCms\Core\Plugin;

/**
 * An utility to generate a sitemap
 */
class SitemapBuilder
{
    /**
     * Gets all executable methods for the `Sitemap` class of a plugin
     * @param string $plugin Plugin
     * @return \Cake\Collection\CollectionInterface Collection of classes and methods names
     */
    public static function getMethods(string $plugin): CollectionInterface
    {
        $class = $plugin . '\Utility\Sitemap\Sitemap';

        return collection(get_child_methods($class) ?: [])->map(function (string $name) use ($class) {
            return compact('class', 'name');
        });
    }

    /**
     * Generate the sitemap.
     *
     * For each plugin, calls dynamically all executable methods for the
     *  `Sitemap` class, if that exists.
     *
     * Each method must return an array of url which will be added to the sitemap.
     * @return string
     * @see \MeCms\Utility\Sitemap\Sitemap
     */
    public static function generate(): string
    {
        //Adds the homepage
        $url[] = ['loc' => Router::url('/', true), 'priority' => '0.5'];

        foreach (Plugin::all(['mecms_core' => false]) as $plugin) {
            //Calls all executable methods for the `Sitemap` class of a plugin
            foreach (self::getMethods($plugin) as $method) {
                $callable = [$method['class'], $method['name']];
                if (is_callable($callable)) {
                    $url = array_merge($url, (array)call_user_func($callable));
                }
            }
        }

        $xml = Xml::fromArray(['urlset' => [
            'xmlns:' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'url' => $url,
        ]], ['pretty' => true]);

        return trim($xml->asXML() ?: '');
    }
}
