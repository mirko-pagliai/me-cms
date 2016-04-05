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
 */
namespace MeCms\Utility;

use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use Cake\Utility\Xml;

class Sitemap {    
    protected function _pages() {
        $url = [];
        
        $pages = TableRegistry::get('MeCms.Pages')->find('active')->select(['slug']);
        
        if(!$pages->isEmpty()) {
            //Pages index
            $url[] = self::url(['_name' => 'pages']);

            //All pages
            $url = am($url, array_map(function($page) {
                return self::url(['_name' => 'page', $page->slug]);
            }, $pages->toArray()));
        }
        
        $statics = \MeCms\Utility\StaticPage::all();
        
        //All static pages
        $url = am($url, array_map(function($page) {
            return self::url(['_name' => 'page', $page->slug]);
        }, $statics));
        
        return $url;
    }


    protected function _photos() {
        $url = [];
        
        $albums = TableRegistry::get('MeCms.PhotosAlbums')->find('active')
            ->select(['id', 'slug'])
            ->contain(['Photos' => function($q) {
                return $q->select(['id', 'album_id']);
            }]);
        
        //No albums, no photos. Return
        if($albums->isEmpty())
            return [];
        
        //Albums index
        $url[] = self::url(['_name' => 'albums']);

        foreach($albums->toArray() as $album) {
            //All albums
            $url[] = self::url(['_name' => 'album', $album->slug]);

            //For each album, all photos
            $url = am($url, array_map(function($photo) {
                return self::url(['_name' => 'photo', $photo->id]);
            }, $album->photos));
        }
            
        return $url;
    }
    
    protected function _posts() {
        $table = TableRegistry::get('MeCms.PostsCategories');
        $url = [];
        
        $categories = $table->find('active')
            ->select(['id', 'slug'])
            ->contain(['Posts' => function($q) {
                return $q->select(['category_id', 'slug']);
            }]);
        
        //No categories, no posts. Return
        if($categories->isEmpty())
            return [];
        
        //Posts index
        $url[] = self::url(['_name' => 'posts']);
        
        //Categories index
        $url[] = self::url(['_name' => 'posts_categories']);
        
        foreach($categories as $category) {
            //All categories
            $url[] = self::url(['_name' => 'posts_category', $category->slug]);
            
            //For each category, all posts
            $url = am($url, array_map(function($post) {
                return self::url(['_name' => 'post', $post->slug]);
            }, $category->posts));
        }
        
        //Posts search
        $url[] = self::url(['_name' => 'search_posts']);
        
        $tags = $table->Posts->Tags->find('all')
			->order(['tag' => 'ASC'])
			->where(['post_count >' => 0]);
        
        if(!$tags->isEmpty()) {
            //Tags index
            $url[] = self::url(['_name' => 'posts_tags']);
            
            //All tags
            $url = am($url, array_map(function($tag) {
                return self::url(['_name' => 'posts_tag', $tag->slug]);
            }, $tags->toArray()));
        }
        
        return $url;
    }
    
    protected function _system() {
        $url = [];
        
        //Contact form
        if(config('frontend.contact_form'))
            $url[] = self::url(['_name' => 'contact_form']);
        
        return $url;
    }

    protected function url($url) {
        return Router::url($url, TRUE);
    }

    public function generate() {
        //Home page
        $url = [self::url('/')];
        
        $url = am($url, self::_pages(), self::_photos(), self::_posts(), self::_system());
        
        $url = array_map(function($url) {
            return ['loc' => $url];
        }, $url);
                
        $xml = Xml::fromArray(['urlset' => am(compact('url'), ['xmlns:' => 'http://www.sitemaps.org/schemas/sitemap/0.9'])], ['pretty' => TRUE]);
        
        debug($xml->asXML());
        
        exit;
    }
}