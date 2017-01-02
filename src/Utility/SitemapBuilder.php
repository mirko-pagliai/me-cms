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
     * For each plugin, it calls dynamically all methods from class
     * `$PLUGIN\Utility\Sitemap`.
     * Each method must be return an array or urls to add to the sitemap.
     * @return string
     * @see MeCms\Utility\Sitemap
     * @uses MeCms\Core\Plugin::all()
     * @uses parse()
     */
    public function generate()
    {
        //Adds the homepage
        $url = [self::parse('/')];

        foreach (Plugin::all() as $plugin) {
            //Sets the class name
            $class = sprintf('\%s\Utility\Sitemap', $plugin);

            //Gets all methods from `$PLUGIN\Utility\Sitemap` class
            $methods = getChildMethods($class);

            if (empty($methods)) {
                continue;
            }

            //Calls all methods
            foreach ($methods as $method) {
                $url = am($url, call_user_func([$class, $method]));
            }
        }

        $xml = Xml::fromArray(['urlset' => [
            'xmlns:' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'url' => $url,
        ]], ['pretty' => true]);

        return $xml->asXML();
    }
}
