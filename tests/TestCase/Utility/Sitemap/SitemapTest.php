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

namespace MeCms\Test\TestCase\Utility\Sitemap;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\Sitemap\Sitemap;

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
    public function testPages(): void
    {
        $this->loadFixtures('Pages', 'PagesCategories');
        /** @var \MeCms\Model\Table\PagesCategoriesTable $table */
        $table = TableRegistry::getTableLocator()->get('MeCms.PagesCategories');

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

        Configure::write('MeCms.sitemap.pages', false);
        $this->assertEmpty(Sitemap::pages());

        //Deletes all records
        Configure::write('MeCms.sitemap.pages', true);
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::pages());
    }

    /**
     * Test for `posts()` method
     * @test
     */
    public function testPosts(): void
    {
        $this->loadFixtures('Posts', 'PostsCategories');
        /** @var \MeCms\Model\Table\PostsCategoriesTable $table */
        $table = TableRegistry::getTableLocator()->get('MeCms.PostsCategories');

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

        Configure::write('MeCms.sitemap.posts', false);
        $this->assertEmpty(Sitemap::posts());

        //Deletes all records
        Configure::write('MeCms.sitemap.posts', true);
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::posts());
    }

    /**
     * Test for `postsTags()` method
     * @test
     */
    public function testPostsTags(): void
    {
        $this->loadFixtures('Posts', 'PostsTags', 'Tags');
        /** @var \MeCms\Model\Table\TagsTable $table */
        $table = TableRegistry::getTableLocator()->get('MeCms.Tags');

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

        Configure::write('MeCms.sitemap.posts_tags', false);
        $this->assertEmpty(Sitemap::postsTags());

        //Deletes all records
        Configure::write('MeCms.sitemap.posts_tags', true);
        $table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::postsTags());
    }

    /**
     * Test for `staticPages()` method
     * @test
     */
    public function testStaticPages(): void
    {
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

        Configure::write('MeCms.sitemap.static_pages', false);
        $this->assertEmpty(Sitemap::staticPages());
        Configure::write('MeCms.sitemap.static_pages', true);
    }

    /**
     * Test for `systems()` method
     * @test
     */
    public function testSystems(): void
    {
        $this->assertEquals([[
            'loc' => 'http://localhost/contact/us',
            'priority' => '0.5',
        ]], Sitemap::systems());

        Configure::write('MeCms.sitemap.systems', false);
        $this->assertEmpty(Sitemap::systems());

        //Disabled contact form
        Configure::write('MeCms.sitemap.systems', true);
        Configure::write('MeCms.default.contact_us', false);
        $this->assertEmpty(Sitemap::systems());
    }
}
