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
     * @return array<array{class: class-string, name: string}> Array with classes and methods names
     * @throws \ErrorException
     */
    public static function getMethods(string $plugin): array
    {
        $class = $plugin . '\Utility\Sitemap\Sitemap';

        return class_exists($class) ? array_map(fn(string $name) => ['class' => $class] + compact('name'), get_child_methods($class)) : [];
    }

    /**
     * Generate the sitemap.
     *
     * For each plugin, calls dynamically all executable methods for the `Sitemap` class, if that exists.
     *
     * Each method must return an array of url which will be added to the sitemap.
     * @return string
     * @throws \ErrorException
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
                    $url = [...$url, ...(array)call_user_func($callable)];
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
