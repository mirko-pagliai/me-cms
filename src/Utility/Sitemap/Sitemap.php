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

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use MeCms\Utility\Sitemap\SitemapBase;
use MeCms\Utility\StaticPage;

/**
 * This class contains methods called by the `SitemapBuilder`.
 * Each method must be return an array or urls to add to the sitemap.
 *
 * This class contains methods that will be called automatically.
 * You do not need to call these methods manually.
 */
class Sitemap extends SitemapBase
{
    /**
     * Returns pages urls
     * @return array
     */
    public static function pages(): array
    {
        if (!getConfig('sitemap.pages')) {
            return [];
        }

        /** @var \MeCms\Model\Table\PagesCategoriesTable $table */
        $table = TableRegistry::get('MeCms.PagesCategories');
        $url = Cache::read('sitemap', $table->getCacheName());

        if (!$url) {
            $categories = $table->find('active')
                ->select(['id', 'lft', 'slug'])
                ->contain($table->Pages->getAlias(), function (Query $query) {
                    return $query->find('active')
                        ->select(['category_id', 'slug', 'modified'])
                        ->orderDesc('modified');
                })
                ->orderAsc(sprintf('%s.lft', $table->getAlias()));

            if ($categories->isEmpty()) {
                return [];
            }

            //Adds categories index
            $url = [self::parse(['_name' => 'pagesCategories'])];

            foreach ($categories as $category) {
                //Adds category
                $url[] = self::parse(
                    ['_name' => 'pagesCategory', $category->get('slug')],
                    ['lastmod' => array_value_first($category->get('pages'))->get('modified')]
                );

                //Adds each page
                foreach ($category->get('pages') as $page) {
                    $url[] = self::parse(['_name' => 'page', $page->get('slug')], ['lastmod' => $page->get('modified')]);
                }
            }

            Cache::write('sitemap', $url, $table->getCacheName());
        }

        return $url;
    }

    /**
     * Returns posts urls
     * @return array
     */
    public static function posts(): array
    {
        if (!getConfig('sitemap.posts')) {
            return [];
        }

        /** @var \MeCms\Model\Table\PostsCategoriesTable $table */
        $table = TableRegistry::get('MeCms.PostsCategories');
        $url = Cache::read('sitemap', $table->getCacheName());

        if (!$url) {
            $categories = $table->find('active')
                ->select(['id', 'lft', 'slug'])
                ->contain($table->Posts->getAlias(), function (Query $query) {
                    return $query->find('active')
                        ->select(['category_id', 'slug', 'modified'])
                        ->orderDesc('modified');
                })
                ->orderAsc(sprintf('%s.lft', $table->getAlias()));

            if ($categories->isEmpty()) {
                return [];
            }

            /** @var \MeCms\Model\Entity\Post $latest */
            $latest = $table->Posts->find('active')
                ->select(['modified'])
                ->orderDesc('modified')
                ->firstOrFail();

            //Adds posts index, categories index and posts search
            $url = [
                self::parse(['_name' => 'posts'], ['lastmod' => $latest->get('modified')]),
                self::parse(['_name' => 'postsCategories']),
                self::parse(['_name' => 'postsSearch'], ['priority' => '0.2']),
            ];

            foreach ($categories as $category) {
                //Adds category
                $url[] = self::parse(
                    ['_name' => 'postsCategory', $category->get('slug')],
                    ['lastmod' => array_value_first($category->get('posts'))->get('modified')]
                );

                //Adds each post
                foreach ($category->get('posts') as $post) {
                    $url[] = self::parse(['_name' => 'post', $post->get('slug')], ['lastmod' => $post->get('modified')]);
                }
            }

            Cache::write('sitemap', $url, $table->getCacheName());
        }

        return $url;
    }

    /**
     * Returns posts tags urls
     * @return array
     */
    public static function postsTags(): array
    {
        if (!getConfig('sitemap.posts_tags')) {
            return [];
        }

        /** @var \MeCms\Model\Table\TagsTable $table */
        $table = TableRegistry::get('MeCms.Tags');
        $url = Cache::read('sitemap', $table->getCacheName());

        if (!$url) {
            $tags = $table->find('active')
                ->select(['tag', 'modified'])
                ->orderAsc(sprintf('%s.tag', $table->getAlias()));

            if ($tags->isEmpty()) {
                return [];
            }

            //Adds tags index
            /** @var \MeCms\Model\Entity\Tag $latest */
            $latest = $table->find()
                ->select(['modified'])
                ->orderDesc('modified')
                ->firstOrFail();
            $url = [self::parse(['_name' => 'postsTags'], ['lastmod' => $latest->get('modified')])];

            //Adds each tag
            foreach ($tags as $tag) {
                $url[] = self::parse(['_name' => 'postsTag', $tag->get('slug')], ['lastmod' => $tag->get('modified')]);
            }

            Cache::write('sitemap', $url, $table->getCacheName());
        }

        return $url;
    }

    /**
     * Returns static pages urls
     * @return array
     * @uses \MeCms\Utility\StaticPage::all()
     */
    public static function staticPages(): array
    {
        if (!getConfig('sitemap.static_pages')) {
            return [];
        }

        return StaticPage::all()->map(function (Entity $page) {
            return self::parse(['_name' => 'page', $page->get('slug')], ['lastmod' => $page->get('modified')]);
        })->toArray();
    }

    /**
     * Returns systems urls
     * @return array
     */
    public static function systems(): array
    {
        if (!getConfig('sitemap.systems') || !getConfig('default.contact_us')) {
            return [];
        }

        return [self::parse(['_name' => 'contactUs'])];
    }
}
