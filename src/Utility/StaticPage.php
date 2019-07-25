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
     * Extension for static pages
     */
    protected const EXTENSION = 'ctp';

    /**
     * Internal method to get all the existing paths
     * @return array
     * @uses \MeCms\Core\Plugin::all()
     * @uses getPaths()
     */
    protected static function getAllPaths(): array
    {
        return Cache::remember('paths', function () {
            $paths = self::getPaths();

            foreach (Plugin::all() as $plugin) {
                $paths = array_merge($paths, self::getPaths($plugin));
            }

            return array_filter($paths, 'file_exists');
        }, 'static_pages');
    }

    /**
     * Internal method to get paths from APP or from a plugin.
     *
     * This also returns paths that do not exist.
     * @param string|null $plugin Plugin name or `null` for APP path
     * @return array
     * @since 2.26.6
     */
    protected static function getPaths(?string $plugin = null): string
    {
        return App::path('Template' . DS . 'StaticPages', $plugin);
    }

    /**
     * Internal method to get the slug.
     *
     * It takes the full path and removes the relative path and the extension.
     * @param string $path Path
     * @param string $relativePath Relative path
     * @return string
     */
    protected static function getSlug(string $path, string $relativePath): string
    {
        if (string_starts_with($path, $relativePath)) {
            $path = substr($path, strlen(add_slash_term($relativePath)));
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
    public static function all(): array
    {
        foreach (self::getAllPaths() as $path) {
            //Gets all files for each path
            $files = (new Folder($path))->findRecursive('^.+\.' . self::EXTENSION . '$', true);

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
     * @return string|null Static page or `null`
     * @uses \MeCms\Core\Plugin::all()
     * @uses getPaths()
     */
    public static function get(string $slug): ?string
    {
        $locale = I18n::getLocale();
        $slug = array_filter(explode('/', $slug));
        $cache = sprintf('page_%s_locale_%s', md5(serialize($slug)), $locale);

        return Cache::remember($cache, function () use ($locale, $slug) {
            //Sets the (partial) filename
            $filename = implode(DS, $slug);

            //Sets the filename patterns
            $patterns = [$filename . '-' . $locale];
            if (preg_match('/^(\w+)_\w+$/', $locale, $matches)) {
                $patterns[] = $filename . '-' . $matches[1];
            }
            $patterns[] = $filename;

            //Checks if the page exists first in APP, then in each plugin
            foreach (array_merge([null], Plugin::all()) as $plugin) {
                foreach (self::getPaths($plugin) as $path) {
                    foreach ($patterns as $pattern) {
                        if (is_readable($path . $pattern . '.' . self::EXTENSION)) {
                            $page = ($plugin ? $plugin . '.' : '') . DS . 'StaticPages' . DS . $pattern;

                            break 3;
                        }
                    }
                }
            }

            return $page ?? null;
        }, 'static_pages');
    }

    /**
     * Gets the title for a static page from its slug or path
     * @param string $slugOrPath Slug or path
     * @return string
     */
    public static function title(string $slugOrPath): string
    {
        //Gets only the filename (without extension), then turns dashes into
        //  underscores (because `Inflector::humanize` will remove only underscores)
        $slugOrPath = pathinfo($slugOrPath, PATHINFO_FILENAME);
        $slugOrPath = str_replace('-', '_', $slugOrPath);

        return Inflector::humanize($slugOrPath);
    }
}
