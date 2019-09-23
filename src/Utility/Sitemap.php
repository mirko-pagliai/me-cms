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
 * @see         MeCms\Utility\SitemapBuilder
 */
namespace MeCms\Utility;

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use MeCms\Utility\SitemapBuilder;
use MeCms\Utility\StaticPage;

/**
 * This class contains methods called by the `SitemapBuilder`.
 * Each method must be return an array or urls to add to the sitemap.
 *
 * This class contains methods that will be called automatically.
 * You do not need to call these methods manually.
 */
class Sitemap extends SitemapBuilder
{
    /**
     * Returns pages urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function pages()
    {
        if (!getConfig('sitemap.pages')) {
            return [];
        }

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
                ->orderAsc('lft');

            if ($categories->isEmpty()) {
                return [];
            }

            //Adds categories index
            $url[] = self::parse(['_name' => 'pagesCategories']);

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
     * Returns photos urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function photos()
    {
        if (!getConfig('sitemap.photos')) {
            return [];
        }

        $table = TableRegistry::get('MeCms.PhotosAlbums');
        $url = Cache::read('sitemap', $table->getCacheName());

        if (!$url) {
            $albums = $table->find('active')
                ->select(['id', 'slug'])
                ->contain($table->Photos->getAlias(), function (Query $query) {
                    return $query->find('active')
                        ->select(['id', 'album_id', 'modified'])
                        ->orderDesc('modified');
                });

            if ($albums->isEmpty()) {
                return [];
            }

            //Adds albums index
            $latest = $table->Photos->find('active')
                ->select(['modified'])
                ->orderDesc('modified')
                ->firstOrFail();
            $url[] = self::parse(['_name' => 'albums'], ['lastmod' => $latest->get('modified')]);

            foreach ($albums as $album) {
                //Adds album
                $url[] = self::parse(
                    ['_name' => 'album', $album->get('slug')],
                    ['lastmod' => array_value_first($album->get('photos'))->get('modified')]
                );

                //Adds each photo
                foreach ($album->get('photos') as $photo) {
                    $url[] = self::parse(
                        ['_name' => 'photo', 'slug' => $album->get('slug'), 'id' => $photo->get('id')],
                        ['lastmod' => $photo->get('modified')]
                    );
                }
            }

            Cache::write('sitemap', $url, $table->getCacheName());
        }

        return $url;
    }

    /**
     * Returns posts urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function posts()
    {
        if (!getConfig('sitemap.posts')) {
            return [];
        }

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
                ->orderAsc('lft');

            if ($categories->isEmpty()) {
                return [];
            }

            $latest = $table->Posts->find('active')
                ->select(['modified'])
                ->orderDesc('modified')
                ->firstOrFail();

            //Adds posts index, categories index and posts search
            $url[] = self::parse(['_name' => 'posts'], ['lastmod' => $latest->get('modified')]);
            $url[] = self::parse(['_name' => 'postsCategories']);
            $url[] = self::parse(['_name' => 'postsSearch'], ['priority' => '0.2']);

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
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function postsTags()
    {
        if (!getConfig('sitemap.posts_tags')) {
            return [];
        }

        $table = TableRegistry::get('MeCms.Tags');
        $url = Cache::read('sitemap', $table->getCacheName());

        if (!$url) {
            $tags = $table->find('active')->select(['tag', 'modified'])->orderAsc('tag');

            if ($tags->isEmpty()) {
                return [];
            }

            //Adds tags index
            $latest = $table->find()
                ->select(['modified'])
                ->orderDesc('modified')
                ->firstOrFail();
            $url[] = self::parse(['_name' => 'postsTags'], ['lastmod' => $latest->get('modified')]);

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
     * @uses MeCms\Utility\SitemapBuilder::parse()
     * @uses MeCms\Utility\StaticPage::all()
     */
    public static function staticPages()
    {
        if (!getConfig('sitemap.static_pages')) {
            return [];
        }

        return array_map(function (Entity $page) {
            return self::parse(['_name' => 'page', $page->get('slug')], ['lastmod' => $page->get('modified')]);
        }, StaticPage::all());
    }

    /**
     * Returns systems urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function systems()
    {
        if (!getConfig('sitemap.systems')) {
            return [];
        }

        //Contact form
        if (getConfig('default.contact_us')) {
            $url[] = self::parse(['_name' => 'contactUs']);
        }

        return isset($url) ? $url : [];
    }
}
