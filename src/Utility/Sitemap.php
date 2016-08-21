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
 * @see         MeCms\Utility\SitemapBuilder
 */
namespace MeCms\Utility;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Utility\SitemapBuilder;

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
     * Method that returns pages urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function pages()
    {
        $table = TableRegistry::get('MeCms.PagesCategories');

        $url = Cache::read('sitemap', $table->cache);

        if ($url) {
            return $url;
        }

        $categories = $table->find('active')
            ->select(['id', 'slug'])
            ->contain(['Pages' => function ($q) use ($table) {
                return $q
                    ->select(['id', 'category_id', 'slug', 'modified'])
                    ->order([sprintf('%s.modified', $table->Pages->alias()) => 'DESC']);
            }]);

        if ($categories->isEmpty()) {
            return [];
        }

        //Adds categories index
        $url[] = self::parse(['_name' => 'pagesCategories']);

        foreach ($categories as $category) {
            //Adds the category
            $url[] = self::parse(
                ['_name' => 'pagesCategory', $category->slug],
                ['lastmod' => $category->pages[0]->modified]
            );

            //Adds the pages
            $url = am($url, array_map(function ($page) {
                return self::parse(
                    ['_name' => 'page', $page->slug],
                    ['lastmod' => $page->modified]
                );
            }, $category->pages));
        }

        Cache::write('sitemap', $url, $table->cache);

        return $url;
    }

    /**
     * Method that returns photos urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function photos()
    {
        $table = TableRegistry::get('MeCms.PhotosAlbums');

        $url = Cache::read('sitemap', $table->cache);

        if ($url) {
            return $url;
        }

        $albums = $table->find('active')
            ->select(['id', 'slug'])
            ->contain(['Photos' => function ($q) use ($table) {
                return $q
                    ->select(['id', 'album_id', 'modified'])
                    ->order([sprintf('%s.modified', $table->Photos->alias()) => 'DESC']);
            }]);

        if ($albums->isEmpty()) {
            return [];
        }

        $latest = $table->Photos->find('active')
            ->select(['modified'])
            ->order([sprintf('%s.modified', $table->Photos->alias()) => 'DESC'])
            ->firstOrFail();

        //Adds albums index
        $url = [
            self::parse(['_name' => 'albums'], ['lastmod' => $latest->modified])
        ];

        foreach ($albums->toArray() as $album) {
            //Adds the album
            $url[] = self::parse(
                ['_name' => 'album', $album->slug],
                ['lastmod' => $album->photos[0]->modified]
            );

            //Adds the photos
            $url = am($url, array_map(function ($photo) use ($album) {
                return self::parse(
                    ['_name' => 'photo', 'slug' => $album->slug, 'id' => $photo->id],
                    ['lastmod' => $photo->modified]
                );
            }, $album->photos));
        }

        Cache::write('sitemap', $url, $table->cache);

        return $url;
    }

    /**
     * Method that returns posts urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function posts()
    {
        $table = TableRegistry::get('MeCms.PostsCategories');

        $url = Cache::read('sitemap', $table->cache);

        if ($url) {
            return $url;
        }

        $categories = $table->find('active')
            ->select(['id', 'slug'])
            ->contain(['Posts' => function ($q) use ($table) {
                return $q
                    ->select(['category_id', 'slug', 'modified'])
                    ->order([sprintf('%s.modified', $table->Posts->alias()) => 'DESC']);
            }]);

        if ($categories->isEmpty()) {
            return [];
        }

        $latest = $table->Posts->find('active')
            ->select(['modified'])
            ->order([sprintf('%s.modified', $table->Posts->alias()) => 'DESC'])
            ->firstOrFail();

        //Adds posts index, categories index and posts search
        $url = [
            self::parse(['_name' => 'posts'], ['lastmod' => $latest->modified]),
            self::parse(['_name' => 'postsCategories']),
            self::parse(['_name' => 'postsSearch'], ['priority' => '0.2']),
        ];

        foreach ($categories as $category) {
            //Adds the category
            $url[] = self::parse(
                ['_name' => 'postsCategory', $category->slug],
                ['lastmod' => $category->posts[0]->modified]
            );

            //Adds the posts
            $url = am($url, array_map(function ($post) {
                return self::parse(
                    ['_name' => 'post', $post->slug],
                    ['lastmod' => $post->modified]
                );
            }, $category->posts));
        }

        Cache::write('sitemap', $url, $table->cache);

        return $url;
    }

    /**
     * Method that returns posts tags urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function postsTags()
    {
        $table = TableRegistry::get('MeCms.Tags');

        $url = Cache::read('sitemap', $table->cache);

        if ($url) {
            return $url;
        }

        $tags = $table->find('all')
            ->select(['tag', 'modified'])
            ->order(['tag' => 'ASC'])
            ->where(['post_count >' => 0]);

        if ($tags->isEmpty()) {
            return [];
        }

        $latest = $table->find()
            ->select(['modified'])
            ->order(['modified' => 'DESC'])
            ->firstOrFail();

        //Adds the tags index
        $url[] = self::parse(
            ['_name' => 'postsTags'],
            ['lastmod' => $latest->modified]
        );

        //Adds all tags
        $url = am($url, array_map(function ($tag) {
            return self::parse(
                ['_name' => 'postsTag', $tag->slug],
                ['lastmod' => $tag->modified]
            );
        }, $tags->toArray()));

        Cache::write('sitemap', $url, $table->cache);

        return $url;
    }

    /**
     * Method that returns static pages urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     * @uses MeCms\Utility\StaticPage::all()
     */
    public static function staticPages()
    {
        $statics = \MeCms\Utility\StaticPage::all();

        //Adds static pages
        $url = array_map(function ($page) {
            return self::parse(
                ['_name' => 'page', $page->slug],
                ['lastmod' => $page->modified]
            );
        }, $statics);

        return $url;
    }

    /**
     * Method that returns systems urls.
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::parse()
     */
    public static function systems()
    {
        $url = [];

        //Contact form
        if (config('default.contact_form')) {
            $url[] = self::parse(['_name' => 'contactForm']);
        }

        return $url;
    }
}
