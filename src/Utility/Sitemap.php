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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @see         MeCms\Utility\SitemapBuilder
 */
namespace MeCms\Utility;

use Cake\ORM\TableRegistry;
use MeCms\Utility\SitemapBuilder;

/**
 * This class contains methods called by the `SitemapBuilder`.
 * Each method must be return an array or urls to add to the sitemap.
 */
class Sitemap extends SitemapBuilder {
    /**
     * Method that returns pages urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::url()
     */
    public static function pages() {
        $pages = TableRegistry::get('MeCms.Pages')->find('active')->select(['slug']);
        
        if($pages->isEmpty()) {
            return [];
        }
        
        //Adds pages index
        $url = [self::url(['_name' => 'pages'])];

        //Adds all pages
        $url = am($url, array_map(function($page) {
            return self::url(['_name' => 'page', $page->slug]);
        }, $pages->toArray()));
        
        return $url;
    }

    /**
     * Method that returns photos urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::url()
     */
    public static function photos() {
        $albums = TableRegistry::get('MeCms.PhotosAlbums')->find('active')
            ->select(['id', 'slug'])
            ->contain(['Photos' => function($q) {
                return $q->select(['id', 'album_id']);
            }]);
        
        if($albums->isEmpty()) {
            return [];
        }
        
        //Adds albums index
        $url = [self::url(['_name' => 'albums'])];

        foreach($albums->toArray() as $album) {
            //Adds the album
            $url[] = self::url(['_name' => 'album', $album->slug]);

            //Adds the photos
            $url = am($url, array_map(function($photo) {
                return self::url(['_name' => 'photo', $photo->id]);
            }, $album->photos));
        }
            
        return $url;
    }
    
    /**
     * Method that returns posts urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::url()
     */
    public static function posts() {        
        $categories = TableRegistry::get('MeCms.PostsCategories')->find('active')
            ->select(['id', 'slug'])
            ->contain(['Posts' => function($q) {
                return $q->select(['category_id', 'slug']);
            }]);
        
        if($categories->isEmpty()) {
            return [];
        }
        
        //Adds posts index, categories index and posts search
        $url = [
            self::url(['_name' => 'posts']),
            self::url(['_name' => 'posts_categories']),
            self::url(['_name' => 'posts_search']),
        ];
        
        foreach($categories as $category) {
            //Adds the category
            $url[] = self::url(['_name' => 'posts_category', $category->slug]);
            
            //Adds the posts
            $url = am($url, array_map(function($post) {
                return self::url(['_name' => 'post', $post->slug]);
            }, $category->posts));
        }
        
        return $url;
    }
    
    /**
     * Method that returns posts tags urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::url()
     */
    public static function posts_tags() {            
        $tags = TableRegistry::get('MeCms.Tags')->find('all')
			->order(['tag' => 'ASC'])
			->where(['post_count >' => 0]);
        
        if($tags->isEmpty()) {
            return [];
        }
        
        //Adds the tags index
        $url[] = self::url(['_name' => 'posts_tags']);

        //Adds all tags
        $url = am($url, array_map(function($tag) {
            return self::url(['_name' => 'posts_tag', $tag->slug]);
        }, $tags->toArray()));
        
        return $url;
    }
    
    /**
     * Method that returns static pages urls
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::url()
     * @uses MeCms\Utility\StaticPage::all()
     */
    public static function static_pages() {
        $statics = \MeCms\Utility\StaticPage::all();
        
        //Adds static pages
        $url = array_map(function($page) {
            return self::url(['_name' => 'page', $page->slug]);
        }, $statics);
        
        return $url;
    }
    
    /**
     * Method that returns systems urls.
     * @return array
     * @uses MeCms\Utility\SitemapBuilder::url()
     */
    public static function systems() {
        $url = [];
        
        //Contact form
        if(config('frontend.contact_form'))
            $url[] = self::url(['_name' => 'contact_form']);
        
        return $url;
    }
}