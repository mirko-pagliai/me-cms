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
 */
namespace MeCms\Test\TestCase\Utility;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use MeCms\Utility\Sitemap;
use MeTools\TestSuite\TestCase;

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
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
        'plugin.me_cms.Photos',
        'plugin.me_cms.PhotosAlbums',
        'plugin.me_cms.Posts',
        'plugin.me_cms.PostsCategories',
        'plugin.me_cms.PostsTags',
        'plugin.me_cms.Tags',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Cache::clearAll();

        Plugin::load('TestPlugin');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload('TestPlugin');
    }

    /**
     * Test for `pages()` method
     * @test
     */
    public function testPages()
    {
        $this->loadFixtures('Pages', 'PagesCategories');

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

        $table = TableRegistry::get(ME_CMS . '.PagesCategories');

        $this->assertEquals($expected, Sitemap::pages());

        $this->assertNotEmpty(Cache::read('sitemap', $table->cache));

        $this->assertEquals($expected, Sitemap::pages());
    }

    /**
     * Test for `pages()` method, with no records
     * @test
     */
    public function testPagesNoRecords()
    {
        $this->loadFixtures('Pages', 'PagesCategories');

        //Deletes all records
        TableRegistry::get(ME_CMS . '.PagesCategories')->deleteAll(['id >=' => 1]);

        $this->assertEmpty(Sitemap::pages());
    }

    /**
     * Test for `photos()` method
     * @test
     */
    public function testPhotos()
    {
        $this->loadFixtures('Photos', 'PhotosAlbums');

        $expected = [
            [
                'loc' => 'http://localhost/albums',
                'lastmod' => '2016-12-28T10:40:42+00:00',
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
        ];

        $table = TableRegistry::get(ME_CMS . '.PhotosAlbums');

        $this->assertEquals($expected, Sitemap::photos());

        $this->assertNotEmpty(Cache::read('sitemap', $table->cache));

        $this->assertEquals($expected, Sitemap::photos());
    }

    /**
     * Test for `photos()` method, with no records
     * @test
     */
    public function testPhotosNoRecords()
    {
        $this->loadFixtures('Photos', 'PhotosAlbums');

        //Deletes all records
        TableRegistry::get(ME_CMS . '.PhotosAlbums')->deleteAll(['id >=' => 1]);

        $this->assertEmpty(Sitemap::photos());
    }

    /**
     * Test for `posts()` method
     * @test
     */
    public function testPosts()
    {
        $this->loadFixtures('Posts', 'PostsCategories');

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

        $table = TableRegistry::get(ME_CMS . '.PostsCategories');

        $this->assertEquals($expected, Sitemap::posts());

        $this->assertNotEmpty(Cache::read('sitemap', $table->cache));

        $this->assertEquals($expected, Sitemap::posts());
    }

    /**
     * Test for `posts()` method, with no records
     * @test
     */
    public function testPostsNoRecords()
    {
        //Deletes all records
        TableRegistry::get(ME_CMS . '.PostsCategories')->deleteAll(['id >=' => 1]);

        $this->assertEmpty(Sitemap::posts());
    }

    /**
     * Test for `postsTags()` method
     * @test
     */
    public function testPostsTags()
    {
        $this->loadFixtures('Posts', 'PostsTags', 'Tags');

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

        $table = TableRegistry::get(ME_CMS . '.Tags');

        $this->assertEquals($expected, Sitemap::postsTags());

        $this->assertNotEmpty(Cache::read('sitemap', $table->cache));

        $this->assertEquals($expected, Sitemap::postsTags());
    }

    /**
     * Test for `postsTags()` method, with no records
     * @test
     */
    public function testPostsTagsNoRecords()
    {
        //Deletes all records
        TableRegistry::get(ME_CMS . '.Tags')->deleteAll(['id >=' => 1]);

        $this->assertEmpty(Sitemap::postsTags());
    }

    /**
     * Test for `staticPages()` method
     * @test
     */
    public function testStaticPages()
    {
        $map = Sitemap::staticPages();

        //It checks here the `lastmod` value and removes it from the array
        foreach ($map as $k => $url) {
            $this->assertEquals($url['lastmod'], (new FrozenTime($url['lastmod']))->format('c'));

            unset($map[$k]['lastmod']);
        }

        $this->assertEquals([
            [
                'loc' => 'http://localhost/page/page-from-app',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/cookies-policy-it',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/cookies-policy',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/test-from-plugin',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/first-folder/page-on-first-from-plugin',
                'priority' => '0.5',
            ],
            [
                'loc' => 'http://localhost/page/first-folder/second_folder/page_on_second_from_plugin',
                'priority' => '0.5',
            ],
        ], $map);
    }

    /**
     * Test for `systems()` method
     * @test
     */
    public function testSystems()
    {
        $this->assertEquals([[
            'loc' => 'http://localhost/contact/us',
            'priority' => '0.5',
        ]], Sitemap::systems());

        //Disabled contact form
        Configure::write(ME_CMS . '.default.contact_us', false);

        $this->assertEmpty(Sitemap::systems());
    }
}
