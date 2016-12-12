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

use Cake\Core\App;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\Utility\Inflector;
use MeCms\Core\Plugin;

/**
 * An utility to handle static pages
 */
class StaticPage
{
    /**
     * Gets all static pages
     * @return array Static pages
     * @uses MeCms\Core\Plugin::all()
     * @uses title()
     */
    public static function all()
    {
        //Adds all plugins to paths, adding first MeCms
        $paths = array_map(function ($plugin) {
            return firstValue(App::path('Template', $plugin)) . 'StaticPages';
        }, Plugin::all());

        //Adds APP to paths
        array_unshift($paths, firstValue(App::path('Template')) . 'StaticPages');

        $pages = [];

        //Gets all static pages
        foreach ($paths as $path) {
            $pages = am($pages, array_map(function ($file) use ($path) {
                return (object)[
                    'filename' => pathinfo($file, PATHINFO_FILENAME),
                    'path' => rtr($file),
                    'slug' => preg_replace(
                        '/\.ctp$/',
                        '',
                        preg_replace(sprintf('/^%s/', preg_quote($path . DS, DS)), null, $file)
                    ),
                    'title' => self::title(pathinfo($file, PATHINFO_FILENAME)),
                    'modified' => new FrozenTime(filemtime($file)),
                ];
            }, (new Folder($path))->findRecursive('^.+\.ctp$', true)));
        }

        return $pages;
    }

    /**
     * Gets a static page
     * @param string $slug Slug
     * @return string|bool Static page or false
     * @uses MeCms\Core\Plugin::all()
     */
    public static function get($slug)
    {
        //Sets the file (partial) name
        $file = implode(DS, af(explode('/', $slug)));

        //Sets the file patterns
        $patterns = [sprintf('%s-%s', $file, I18n::locale()), $file];

        //Checks if the page exists in APP
        foreach ($patterns as $pattern) {
            $file = firstValue(App::path('Template')) . 'StaticPages' . DS . $pattern . '.ctp';

            if (is_readable($file)) {
                return 'StaticPages' . DS . $pattern;
            }
        }

        //Checks if the page exists in all plugins, beginning with MeCms
        foreach (Plugin::all() as $plugin) {
            foreach ($patterns as $pattern) {
                $file = firstValue(App::path('Template', $plugin)) . 'StaticPages' . DS . $pattern . '.ctp';

                if (is_readable($file)) {
                    return sprintf('%s.%s', $plugin, 'StaticPages' . DS . $pattern);
                }
            }
        }

        return false;
    }

    /**
     * Gets the title for a static page
     * @param string $slug Slug
     * @return string
     */
    public static function title($slug)
    {
        $slug = af(explode('/', $slug));
        $slug = str_replace('-', '_', $slug[count($slug) - 1]);

        return Inflector::humanize($slug);
    }
}
