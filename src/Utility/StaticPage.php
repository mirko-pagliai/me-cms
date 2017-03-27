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

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use MeCms\Core\Plugin;

/**
 * An utility to handle static pages
 */
class StaticPage
{
    /**
     * Internal method to get all paths for static pages
     * @uses MeCms\Core\Plugin::all()
     * @return array
     */
    protected static function paths()
    {
        $paths = Cache::read('paths', 'static_pages');

        if (empty($paths)) {
            //Adds all plugins to paths
            $paths = collection(Plugin::all())
                ->map(function ($plugin) {
                    return collection(App::path('Template', $plugin))->first() . 'StaticPages';
                })
                ->filter(function ($path) {
                    return file_exists($path);
                })
                ->toList();

            //Adds APP to paths
            array_unshift($paths, collection(App::path('Template'))->first() . 'StaticPages');

            Cache::write('paths', $paths, 'static_pages');
        }

        return $paths;
    }

    /**
     * Internal method to get the slug.
     *
     * It takes the full path and removes the relative path and the extension.
     * @param string $path Path
     * @param string $relativePath Relative path
     * @return string
     */
    protected static function slug($path, $relativePath)
    {
        return preg_replace([
            sprintf('/^%s/', preg_quote(Folder::slashTerm($relativePath), DS)),
            sprintf('/\.%s$/', pathinfo($path, PATHINFO_EXTENSION)),
        ], null, $path);
    }

    /**
     * Gets all static pages
     * @return array Static pages
     * @uses paths()
     * @uses slug()
     * @uses title()
     */
    public static function all()
    {
        foreach (self::paths() as $path) {
            //Gets all files for each path
            $files = (new Folder($path))->findRecursive('^.+\.ctp$', true);

            foreach ($files as $file) {
                $pages[] = new Entity([
                    'filename' => pathinfo($file, PATHINFO_FILENAME),
                    'path' => rtr($file),
                    'slug' => self::slug($file, $path),
                    'title' => self::title(pathinfo($file, PATHINFO_FILENAME)),
                    'modified' => new FrozenTime(filemtime($file)),
                ]);
            }
        }

        return $pages;
    }

    /**
     * Gets a static page
     * @param string $slug Slug
     * @return string|bool Static page or `false`
     * @uses MeCms\Core\Plugin::all()
     */
    public static function get($slug)
    {
        $locale = I18n::locale();

        //Sets the cache name
        $cache = sprintf('page_%s_locale_%s', md5($slug), $locale);

        $page = Cache::read($cache, 'static_pages');

        if (empty($page)) {
            //Sets the (partial) filename
            $filename = implode(DS, af(explode('/', $slug)));

            //Sets the filename patterns
            $patterns = [$filename . '-' . $locale, $filename];

            //Checks if the page exists in APP
            foreach ($patterns as $pattern) {
                $filename = collection(App::path('Template'))->first() . 'StaticPages' . DS . $pattern . '.ctp';

                if (is_readable($filename)) {
                    $page = 'StaticPages' . DS . $pattern;

                    break;
                }
            }

            //Checks if the page exists in each plugin
            foreach (Plugin::all() as $plugin) {
                foreach ($patterns as $pattern) {
                    $filename = collection(App::path('Template', $plugin))->first() . 'StaticPages' . DS . $pattern . '.ctp';

                    if (is_readable($filename)) {
                        $page = $plugin . '.' . 'StaticPages' . DS . $pattern;

                        break;
                    }
                }
            }

            Cache::write($cache, $page, 'static_pages');
        }

        return $page;
    }

    /**
     * Gets the title for a static page from its slug or path
     * @param string $slugOrPath Slug or path
     * @return string
     */
    public static function title($slugOrPath)
    {
        //Gets only the filename (without extension)
        $slugOrPath = pathinfo($slugOrPath, PATHINFO_FILENAME);

        //Turns dashes into underscores (because `Inflector::humanize` will
        //  remove only underscores)
        $slugOrPath = str_replace('-', '_', $slugOrPath);

        return Inflector::humanize($slugOrPath);
    }
}
