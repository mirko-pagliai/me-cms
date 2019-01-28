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
            $alias = $table->Pages->getAlias();

            $categories = $table->find('active')
                ->select(['id', 'lft', 'slug'])
                ->contain($alias, function (Query $q) use ($alias) {
                    return $q->find('active')
                        ->select(['category_id', 'slug', 'modified'])
                        ->order([sprintf('%s.modified', $alias) => 'DESC']);
                })
                ->order(['lft' => 'ASC']);

            if ($categories->isEmpty()) {
                return [];
            }

            //Adds categories index
            $url[] = self::parse(['_name' => 'pagesCategories']);

            foreach ($categories as $category) {
                //Adds the category
                $url[] = self::parse(
                    ['_name' => 'pagesCategory', $category->slug],
                    ['lastmod' => array_value_first($category->pages)->modified]
                );

                //Adds each page
                foreach ($category->pages as $page) {
                    $url[] = self::parse(['_name' => 'page', $page->slug], ['lastmod' => $page->modified]);
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
            $alias = $table->Photos->getAlias();

            $albums = $table->find('active')
                ->select(['id', 'slug'])
                ->contain($alias, function (Query $q) use ($alias) {
                    return $q->find('active')
                        ->select(['id', 'album_id', 'modified'])
                        ->order([sprintf('%s.modified', $alias) => 'DESC']);
                });

            if ($albums->isEmpty()) {
                return [];
            }

            $latest = $table->Photos->find('active')
                ->select(['modified'])
                ->order([sprintf('%s.modified', $alias) => 'DESC'])
                ->firstOrFail();

            //Adds albums index
            $url[] = self::parse(['_name' => 'albums'], ['lastmod' => $latest->modified]);

            foreach ($albums as $album) {
                //Adds the album
                $url[] = self::parse(['_name' => 'album', $album->slug], ['lastmod' => array_value_first($album->photos)->modified]);

                //Adds each photo
                foreach ($album->photos as $photo) {
                    $url[] = self::parse(
                        ['_name' => 'photo', 'slug' => $album->slug, 'id' => $photo->id],
                        ['lastmod' => $photo->modified]
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
            $alias = $table->Posts->getAlias();
            $categories = $table->find('active')
                ->select(['id', 'lft', 'slug'])
                ->contain($alias, function (Query $q) use ($alias) {
                    return $q->find('active')
                        ->select(['category_id', 'slug', 'modified'])
                        ->order([sprintf('%s.modified', $alias) => 'DESC']);
                })
                ->order(['lft' => 'ASC']);

            if ($categories->isEmpty()) {
                return [];
            }

            $latest = $table->Posts->find('active')
                ->select(['modified'])
                ->order([sprintf('%s.modified', $alias) => 'DESC'])
                ->firstOrFail();

            //Adds posts index, categories index and posts search
            $url[] = self::parse(['_name' => 'posts'], ['lastmod' => $latest->modified]);
            $url[] = self::parse(['_name' => 'postsCategories']);
            $url[] = self::parse(['_name' => 'postsSearch'], ['priority' => '0.2']);

            foreach ($categories as $category) {
                //Adds the category
                $url[] = self::parse(
                    ['_name' => 'postsCategory', $category->slug],
                    ['lastmod' => array_value_first($category->posts)->modified]
                );

                //Adds each post
                foreach ($category->posts as $post) {
                    $url[] = self::parse(['_name' => 'post', $post->slug], ['lastmod' => $post->modified]);
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
            $tags = $table->find('active')
                ->select(['tag', 'modified'])
                ->order(['tag' => 'ASC']);

            if ($tags->isEmpty()) {
                return [];
            }

            $latest = $table->find()
                ->select(['modified'])
                ->order(['modified' => 'DESC'])
                ->firstOrFail();

            //Adds the tags index
            $url[] = self::parse(['_name' => 'postsTags'], ['lastmod' => $latest->modified]);

            //Adds each tag
            foreach ($tags as $tag) {
                $url[] = self::parse(['_name' => 'postsTag', $tag->slug], ['lastmod' => $tag->modified]);
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
            return self::parse(['_name' => 'page', $page->slug], ['lastmod' => $page->modified]);
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

        $url = [];

        //Contact form
        if (getConfig('default.contact_us')) {
            $url[] = self::parse(['_name' => 'contactUs']);
        }

        return $url;
    }
}
