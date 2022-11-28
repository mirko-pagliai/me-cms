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
use Symfony\Component\Finder\SplFileInfo;
use Tools\Filesystem;

/**
 * A utility to handle static pages
 */
class StaticPage
{
    /**
     * Extension for static pages
     */
    public const EXTENSION = 'php';

    /**
     * Gets all the existing paths
     * @return array<array-key, string>
     */
    public static function getPaths(): array
    {
        return Cache::remember('paths', function (): array {
            $paths = array_map(fn(string $plugin): array => [$plugin, Plugin::templatePath($plugin) . 'StaticPages'], Plugin::all(['mecms_core' => false]));
            array_unshift($paths, ['App', array_value_first(App::path('templates')) . 'StaticPages']);

            return array_clean(array_combine(array_column($paths, 0), array_column($paths, 1)), 'file_exists');
        }, 'static_pages');
    }

    /**
     * Gets all static pages
     * @return \Cake\Collection\CollectionInterface Collection of static pages
     * @throws \ErrorException
     */
    public static function all(): CollectionInterface
    {
        $getSlug = fn(string $relativePathname): string => str_replace('\\', '/', substr($relativePathname, 0, strrpos($relativePathname, '.' . self::EXTENSION) ?: null));

        /** @var array<\Symfony\Component\Finder\SplFileInfo> $files */
        $files = iterator_to_array((new Finder())->in(self::getPaths())->name('*.' . self::EXTENSION));

        return collection(array_values(array_map(fn(SplFileInfo $file): Entity => new Entity([
            'filename' => pathinfo($file->getPathname(), PATHINFO_FILENAME),
            'path' => Filesystem::instance()->rtr($file->getPathname()),
            'slug' => $getSlug($file->getRelativePathname()),
            'title' => self::getTitle($file->getPathname()),
            'modified' => new FrozenTime($file->getMTime()),
        ]), $files)));
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
        return Inflector::humanize(str_replace('-', '_', pathinfo($slugOrPath, PATHINFO_FILENAME)));
    }
}
