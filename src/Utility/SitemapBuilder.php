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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Utility;

use Cake\Utility\Xml;
use MeCms\Core\Plugin;

/**
 * An utility to generate a sitemap
 */
class SitemapBuilder {
    /**
     * Internal method to generate url for the sitempa
     * @param string|array|null $url
     * @return string
     * @see Cake\Routing\Router::url()
     */
    protected static function url($url) {
        return \Cake\Routing\Router::url($url, TRUE);
    }

    /**
     * Generate the sitemap.
     * 
     * For each plugin, it calls dynamically all methods from class 
     * `$PLUGIN\Utility\Sitemap`.
     * Each method must be return an array or urls to add to the sitemap.
     * @see MeCms\Utility\Sitemap
     * @uses MeCms\Core\Plugin::all()
     * @uses url()
     */
    public function generate() {
        //Adds the homepage
        $url = [self::url('/')];
        
        foreach(am(['MeCms'], Plugin::all(['DebugKit', 'MeCms', 'MeTools', 'Migrations'])) as $plugin) {
            //Sets the class name
            $class = sprintf('\%s\Utility\Sitemap', $plugin);
            
            //Gets all methods from `$PLUGIN\Utility\Sitemap` class
            $methods = get_class_methods($class);
            
            if(empty($methods)) {
                continue;
            }
            
            //Because each class may be an extension of this class, 
            //it calculates the difference between the methods of the two classes
            $methods = array_diff($methods, get_class_methods(__CLASS__));
            
            //Calls all methods 
            foreach($methods as $method) {
                $url = am($url, call_user_func([$class, $method]));
            }
        }
                
        $xml = Xml::fromArray(['urlset' => [
            'xmlns:' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'url' => array_map(function($url) { return ['loc' => $url]; }, $url),
        ]], ['pretty' => TRUE]);
        
        return $xml->asXML();
    }
}