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
     * @return array
     * @uses MeCms\Core\Plugin::all()
     * @uses getAppPath()
     * @uses getPluginPath()
     */
    protected static function getAllPaths()
    {
        return Cache::remember('paths', function () {
            //Adds all plugins to paths
            $plugins = array_map([__CLASS__, 'getPluginPath'], Plugin::all());
            $plugins = array_values(array_filter($plugins, 'file_exists'));

            //Adds the APP path and returns
            return array_merge([self::getAppPath()], $plugins);
        }, 'static_pages');
    }

    /**
     * Internal method to get the app path
     * @return string
     * @since 2.17.1
     */
    protected static function getAppPath()
    {
        return array_value_first(App::path('Template/StaticPages'));
    }

    /**
     * Internal method to get a plugin path
     * @param string $plugin Plugin name
     * @return string
     * @since 2.17.1
     */
    protected static function getPluginPath($plugin)
    {
        return array_value_first(App::path('Template/StaticPages', $plugin));
    }

    /**
     * Internal method to get the slug.
     *
     * It takes the full path and removes the relative path and the extension.
     * @param string $path Path
     * @param string $relativePath Relative path
     * @return string
     */
    protected static function getSlug($path, $relativePath)
    {
        if (string_starts_with($path, $relativePath)) {
            $path = substr($path, strlen(Folder::slashTerm($relativePath)));
        }
        $path = preg_replace(sprintf('/\.[^\.]+$/'), null, $path);

        return IS_WIN ? str_replace(DS, '/', $path) : $path;
    }

    /**
     * Gets all static pages
     * @return array Static pages
     * @uses getAllPaths()
     * @uses getSlug()
     * @uses title()
     */
    public static function all()
    {
        foreach (self::getAllPaths() as $path) {
            //Gets all files for each path
            $files = (new Folder($path))->findRecursive('^.+\.ctp$', true);

            foreach ($files as $file) {
                $pages[] = new Entity([
                    'filename' => pathinfo($file, PATHINFO_FILENAME),
                    'path' => rtr($file),
                    'slug' => self::getSlug($file, $path),
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
     * @uses getAppPath()
     * @uses getPluginPath()
     */
    public static function get($slug)
    {
        $locale = I18n::getLocale();
        $slug = array_filter(explode('/', $slug));
        $cache = sprintf('page_%s_locale_%s', md5(serialize($slug)), $locale);

        return Cache::remember($cache, function() use ($locale, $slug) {
            //Sets the (partial) filename
            $filename = implode(DS, $slug);

            //Sets the filename patterns
            $patterns = [$filename . '-' . $locale];
            if (preg_match('/^(\w+)_\w+$/', $locale, $matches)) {
                $patterns[] = $filename . '-' . $matches[1];
            }
            $patterns[] = $filename;

            //Checks if the page exists in APP
            foreach ($patterns as $pattern) {
                $filename = self::getAppPath() . $pattern . '.ctp';

                if (is_readable($filename)) {
                    $page = DS . 'StaticPages' . DS . $pattern;

                    break;
                }
            }

            //Checks if the page exists in each plugin
            foreach (Plugin::all() as $plugin) {
                foreach ($patterns as $pattern) {
                    $filename = self::getPluginPath($plugin) . $pattern . '.ctp';

                    if (is_readable($filename)) {
                        $page = $plugin . '.' . DS . 'StaticPages' . DS . $pattern;

                        break;
                    }
                }
            }

            return isset($page) ? $page : false;
        }, 'static_pages');
    }

    /**
     * Gets the title for a static page from its slug or path
     * @param string $slugOrPath Slug or path
     * @return string
     */
    public static function title($slugOrPath)
    {
        //Gets only the filename (without extension), then turns dashes into
        //  underscores (because `Inflector::humanize` will remove only underscores)
        $slugOrPath = pathinfo($slugOrPath, PATHINFO_FILENAME);
        $slugOrPath = str_replace('-', '_', $slugOrPath);

        return Inflector::humanize($slugOrPath);
    }
}
