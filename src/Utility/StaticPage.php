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
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;
use MeCms\Core\Plugin;
use Symfony\Component\Finder\Finder;
use Tools\Filesystem;

/**
 * An utility to handle static pages
 */
class StaticPage
{
    /**
     * Extension for static pages
     */
    public const EXTENSION = 'php';

    /**
     * Internal method to get paths from APP or from a plugin.
     *
     * This also returns paths that do not exist.
     * @param string|null $plugin Plugin name or `null` for APP path
     * @return array
     * @since 2.26.6
     */
    protected static function _getPaths(?string $plugin = null): array
    {
        if ($plugin) {
            return [Plugin::templatePath($plugin) . 'StaticPages' . DS];
        }

        return array_map(function ($path) {
            return (new Filesystem())->addSlashTerm($path) . 'StaticPages' . DS;
        }, Configure::read('App.paths.templates'));
    }

    /**
     * Gets all the existing paths
     * @return array
     * @uses \MeCms\Core\Plugin::all()
     * @uses _getPaths()
     */
    public static function getPaths(): array
    {
        return Cache::remember('paths', function () {
            $paths = self::_getPaths();

            foreach (Plugin::all() as $plugin) {
                $paths = array_merge($paths, self::_getPaths($plugin));
            }

            return array_clean(array_map(function ($path) {
                return rtrim($path, DS);
            }, $paths), 'file_exists');
        }, 'static_pages');
    }

    /**
     * Gets the slug for a page.
     *
     * It takes the full path and removes the relative path and the extension.
     * @param string $path Path
     * @param string $relativePath Relative path
     * @return string
     */
    public static function getSlug(string $path, string $relativePath): string
    {
        if (string_starts_with($path, $relativePath)) {
            $path = substr($path, strlen((new Filesystem())->addSlashTerm($relativePath)));
        }
        $path = preg_replace(sprintf('/\.[^\.]+$/'), null, $path);

        return IS_WIN ? str_replace(DS, '/', $path) : $path;
    }

    /**
     * Gets all static pages
     * @return array Static pages
     * @uses getPaths()
     * @uses getSlug()
     * @uses getTitle()
     */
    public static function all(): array
    {
        foreach (self::getPaths() as $path) {
            $finder = new Finder();
            foreach ($finder->files()->name('/^.+\.' . self::EXTENSION . '$/')->sortByName()->in($path) as $file) {
                $pages[] = new Entity([
                    'filename' => pathinfo($file->getPathname(), PATHINFO_FILENAME),
                    'path' => (new Filesystem())->rtr($file->getPathname()),
                    'slug' => self::getSlug($file->getPathname(), $path),
                    'title' => self::getTitle(pathinfo($file->getPathname(), PATHINFO_FILENAME)),
                    'modified' => new FrozenTime($file->getMTime()),
                ]);
            }
        }

        return $pages ?? [];
    }

    /**
     * Gets a static page
     * @param string $slug Slug
     * @return string|bool Static page or `false`
     * @uses _getPaths()
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
                foreach (self::_getPaths($plugin) as $path) {
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
    public static function getTitle(string $slugOrPath): string
    {
        //Gets only the filename (without extension), then turns dashes into
        //  underscores (because `Inflector::humanize` will remove only underscores)
        $slugOrPath = pathinfo($slugOrPath, PATHINFO_FILENAME);
        $slugOrPath = str_replace('-', '_', $slugOrPath);

        return Inflector::humanize($slugOrPath);
    }
}
