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
use Cake\Collection\CollectionInterface;
use Cake\Core\App;
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
     * Gets all the existing paths
     * @return array
     */
    public static function getPaths(): array
    {
        return Cache::remember('paths', function (): array {
            $paths['App'] = array_value_first(App::path('templates')) . 'StaticPages';

            foreach (Plugin::all(['mecms_core' => false]) as $plugin) {
                $paths[$plugin] = Plugin::templatePath($plugin) . 'StaticPages';
            }

            return array_clean($paths, 'file_exists');
        }, 'static_pages');
    }

    /**
     * Gets the slug for a page.
     *
     * It takes the full path and removes relative path and extension.
     * @param string $path Path
     * @param string $relativePath Relative path
     * @return string
     */
    public static function getSlug(string $path, string $relativePath): string
    {
        if (str_starts_with($path, $relativePath)) {
            $path = substr($path, strlen(Filesystem::instance()->addSlashTerm($relativePath)));
        }
        $path = preg_replace(sprintf('/\.[^\.]+$/'), '', $path);

        return IS_WIN ? str_replace(DS, '/', $path) : $path;
    }

    /**
     * Gets all static pages
     * @return \Cake\Collection\CollectionInterface Collection of static pages
     */
    public static function all(): CollectionInterface
    {
        foreach (self::getPaths() as $path) {
            $finder = (new Finder())->files()->name('/^.+\.' . self::EXTENSION . '$/')->sortByName();
            foreach ($finder->in($path) as $file) {
                $pages[] = new Entity([
                    'filename' => pathinfo($file->getPathname(), PATHINFO_FILENAME),
                    'path' => Filesystem::instance()->rtr($file->getPathname()),
                    'slug' => self::getSlug($file->getPathname(), $path),
                    'title' => self::getTitle(pathinfo($file->getPathname(), PATHINFO_FILENAME)),
                    'modified' => new FrozenTime($file->getMTime()),
                ]);
            }
        }

        return collection($pages ?? []);
    }

    /**
     * Gets a static page
     * @param string $slug Slug
     * @return string|null Static page or `null`
     */
    public static function get(string $slug): ?string
    {
        $locale = I18n::getLocale();
        $slug = array_filter(explode('/', $slug));
        $cache = sprintf('page_%s_locale_%s', md5(serialize($slug)), $locale);

        return Cache::remember($cache, function () use ($locale, $slug): ?string {
            //Sets the valid filename patterns
            $filename = implode(DS, $slug);
            $patterns = [$filename . '-' . $locale];
            if (preg_match('/^(\w+)_\w+$/', $locale, $matches)) {
                $patterns[] = $filename . '-' . $matches[1];
            }
            $patterns[] = $filename;
            foreach (self::getPaths() as $plugin => $path) {
                foreach ($patterns as $pattern) {
                    $file = Filesystem::instance()->concatenate($path, $pattern . '.' . self::EXTENSION);
                    if (is_readable($file)) {
                        return ($plugin != 'App' ? $plugin . '.' : '') . DS . 'StaticPages' . DS . $pattern;
                    }
                }
            }

            return null;
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
