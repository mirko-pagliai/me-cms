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

namespace MeCms\Utility;

use Cake\Collection\Collection;
use Cake\Core\App;
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
     * @return \Cake\Collection\Collection Collection of classes and methods names
     */
    public static function getMethods(string $plugin): Collection
    {
        $class = App::classname($plugin . '.Sitemap', 'Utility');
        $methods = $class ? get_child_methods($class) : [];

        return collection($methods)->map(function (string $name) use ($class) {
            return compact('class', 'name');
        });
    }

    /**
     * Internal method to parse each  url
     * @param string|array|null $url Url
     * @param array $options Options, for example `lastmod` or `priority`
     * @return array
     */
    protected static function parse($url, array $options = []): array
    {
        if (!empty($options['lastmod']) && !is_string($options['lastmod'])) {
            $options['lastmod'] = $options['lastmod']->format('c');
        }
        $options += ['priority' => '0.5'];

        return array_merge(['loc' => Router::url($url, true)], $options);
    }

    /**
     * Generate the sitemap.
     *
     * For each plugin, calls dynamically all executable methods for the
     *  `Sitemap` class, if that exists.
     *
     * Each method must return an array of url which will be added to the sitemap.
     * @return string
     * @see \MeCms\Utility\Sitemap
     */
    public static function generate(): string
    {
        //Adds the homepage
        $url[] = self::parse('/');

        foreach (Plugin::all() as $plugin) {
            //Calls all executable methods for the `Sitemap` class of a plugin
            foreach (self::getMethods($plugin) as $method) {
                $url = array_merge($url, (array)call_user_func([$method['class'], $method['name']]));
            }
        }

        $xml = Xml::fromArray(['urlset' => [
            'xmlns:' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'url' => $url,
        ]], ['pretty' => true]);

        return trim($xml->asXML());
    }
}
