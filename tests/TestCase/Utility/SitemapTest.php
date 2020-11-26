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

namespace MeCms\Test\TestCase\Utility;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\Sitemap;

/**
 * SitemapTest class
 */
class SitemapTest extends TestCase
{
    /**
     * Does not automatically load fixtures
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
        'plugin.MeCms.Photos',
        'plugin.MeCms.PhotosAlbums',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.PostsTags',
        'plugin.MeCms.Tags',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Cache::clearAll();

        $this->loadPlugins(['TestPlugin']);
    }

    /**
     * Test for `pages()` method
     * @test
     */
    public function testPages()
    {
        $this->loadFixtures('Pages', 'PagesCategories');
        $table = TableRegistry::getTableLocator()->get('MeCms.PagesCategories');

        //Pages are disabled for the sitemap
        Configure::write('MeCms.sitemap.pages', false);
        $this->assertEmpty(Sitemap::pages());
        Configure::write('MeCms.sitemap.pages', true);

        $expected = [
            [
                'loc' => 'http://localhost/pages/categories',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/pages/category/first-page-category',
                'lastmod' => '2016-12-26T17:30:20+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/second-page',
                'lastmod' => '2016-12-26T17:30:20+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/pages/category/sub-sub-page-category',
                'lastmod' => '2016-12-26T17:29:20+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/first-page',
                'lastmod' => '2016-12-26T17:29:20+00:00',
                'priority' => '0.5',
            ],
        ];
        $this->assertEquals($expected, Sitemap::pages());
        $this->assertEquals($expected, Cache::read('sitemap', $table->getCacheName()));

        //Deletes all records
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::pages());
    }

    /**
     * Test for `photos()` method
     * @test
     */
    public function testPhotos()
    {
        $this->loadFixtures('Photos', 'PhotosAlbums');
        $table = TableRegistry::getTableLocator()->get('MeCms.PhotosAlbums');

        //Photos are disabled for the sitemap
        Configure::write('MeCms.sitemap.photos', false);
        $this->assertEmpty(Sitemap::photos());
        Configure::write('MeCms.sitemap.photos', true);

        $expected = [
            [
                'loc' => 'http://localhost/albums',
                'lastmod' => '2016-12-28T10:40:42+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/album/another-album-test',
                'lastmod' => '2016-12-28T10:39:42+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/photo/another-album-test/2',
                'lastmod' => '2016-12-28T10:39:42+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/album/test-album',
                'lastmod' => '2016-12-28T10:40:42+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/photo/test-album/3',
                'lastmod' => '2016-12-28T10:40:42+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/photo/test-album/1',
                'lastmod' => '2016-12-28T10:38:42+00:00',
                'priority' => '0.5',
            ],
        ];
        $this->assertEquals($expected, Sitemap::photos());
        $this->assertEquals($expected, Cache::read('sitemap', $table->getCacheName()));

        //Deletes all records
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::photos());
    }

    /**
     * Test for `posts()` method
     * @test
     */
    public function testPosts()
    {
        $this->loadFixtures('Posts', 'PostsCategories');
        $table = TableRegistry::getTableLocator()->get('MeCms.PostsCategories');

        //Posts are disabled for the sitemap
        Configure::write('MeCms.sitemap.posts', false);
        $this->assertEmpty(Sitemap::posts());
        Configure::write('MeCms.sitemap.posts', true);

        $expected = [
            [
                'loc' => 'http://localhost/posts',
                'lastmod' => '2016-12-29T18:59:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/categories',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/search',
                'priority' => '0.2',
            ],
            [
                'loc' => 'http://localhost/posts/category/first-post-category',
                'lastmod' => '2016-12-29T18:59:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/post/seventh-post',
                'lastmod' => '2016-12-29T18:59:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/post/fifth-post',
                'lastmod' => '2016-12-28T18:59:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/post/fourth-post',
                'lastmod' => '2016-12-28T18:58:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/post/third-post',
                'lastmod' => '2016-12-28T18:57:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/category/sub-sub-post-category',
                'lastmod' => '2016-12-28T18:56:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/post/second-post',
                'lastmod' => '2016-12-28T18:56:19+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/post/first-post',
                'lastmod' => '2016-11-28T18:55:19+00:00',
                'priority' => '0.5',
            ],
        ];
        $this->assertEquals($expected, Sitemap::posts());
        $this->assertEquals($expected, Cache::read('sitemap', $table->getCacheName()));

        //Deletes all records
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::posts());
    }

    /**
     * Test for `postsTags()` method
     * @test
     */
    public function testPostsTags()
    {
        $this->loadFixtures('Posts', 'PostsTags', 'Tags');
        $table = TableRegistry::getTableLocator()->get('MeCms.Tags');

        //Posts tags are disabled for the sitemap
        Configure::write('MeCms.sitemap.posts_tags', false);
        $this->assertEmpty(Sitemap::postsTags());
        Configure::write('MeCms.sitemap.posts_tags', true);

        $expected = [
            [
                'loc' => 'http://localhost/posts/tags',
                'lastmod' => '2016-12-29T11:16:31+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/tag/bird',
                'lastmod' => '2016-12-29T11:15:31+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/tag/cat',
                'lastmod' => '2016-12-29T11:13:31+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/tag/dog',
                'lastmod' => '2016-12-29T11:14:31+00:00',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/posts/tag/lion',
                'lastmod' => '2016-12-29T11:16:31+00:00',
                'priority' => '0.5',
            ],
        ];
        $this->assertEquals($expected, Sitemap::postsTags());
        $this->assertEquals($expected, Cache::read('sitemap', $table->getCacheName()));

        //Deletes all records
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::postsTags());
    }

    /**
     * Test for `staticPages()` method
     * @test
     */
    public function testStaticPages()
    {
        //Static pages are disabled for the sitemap
        Configure::write('MeCms.sitemap.static_pages', false);
        $this->assertEmpty(Sitemap::staticPages());
        Configure::write('MeCms.sitemap.static_pages', true);

        $map = Sitemap::staticPages();

        //It checks here the `lastmod` value and removes it from the array
        foreach ($map as $k => $url) {
            $this->assertEquals($url['lastmod'], (new FrozenTime($url['lastmod']))->format('c'));
            unset($map[$k]['lastmod']);
        }

        $this->assertContains(['loc' => 'http://localhost/page/page-from-app', 'priority' => '0.5'], $map);
        $this->assertContains(['loc' => 'http://localhost/page/cookies-policy', 'priority' => '0.5'], $map);
        $this->assertContains(['loc' => 'http://localhost/page/cookies-policy-it', 'priority' => '0.5'], $map);
        $this->assertContains(['loc' => 'http://localhost/page/first-folder/page-on-first-from-plugin', 'priority' => '0.5'], $map);
        $this->assertContains(['loc' => 'http://localhost/page/first-folder/second_folder/page_on_second_from_plugin', 'priority' => '0.5'], $map);
        $this->assertContains(['loc' => 'http://localhost/page/test-from-plugin', 'priority' => '0.5'], $map);
    }

    /**
     * Test for `systems()` method
     * @test
     */
    public function testSystems()
    {
        //System pages are disabled for the sitemap
        Configure::write('MeCms.sitemap.systems', false);
        $this->assertEmpty(Sitemap::systems());
        Configure::write('MeCms.sitemap.systems', true);

        $this->assertEquals([[
            'loc' => 'http://localhost/contact/us',
            'priority' => '0.5',
        ]], Sitemap::systems());

        //Disabled contact form
        Configure::write('MeCms.default.contact_us', false);

        $this->assertEmpty(Sitemap::systems());
    }
}
