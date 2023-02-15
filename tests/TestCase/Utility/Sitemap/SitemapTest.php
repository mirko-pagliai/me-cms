<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
use MeCms\TestSuite\TestCase;
use MeCms\Utility\Sitemap\Sitemap;

/**
 * SitemapTest class
 */
class SitemapTest extends TestCase
{
    /**
     * @var array<string>
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
    protected function setUp(): void
    {
        parent::setUp();

        Cache::clearAll();

        $this->loadPlugins(['TestPlugin' => []]);
    }

    /**
     * @test
     * @uses \MeCms\Utility\Sitemap\Sitemap::pages()
     */
    public function testPages(): void
    {
        /** @var \MeCms\Model\Table\PagesCategoriesTable $Table */
        $Table = $this->getTable('MeCms.PagesCategories');

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
        $this->assertSame($expected, Sitemap::pages());
        $this->assertSame($expected, Cache::read('sitemap', $Table->getCacheName()));

        Configure::write('MeCms.sitemap.pages', false);
        $this->assertEmpty(Sitemap::pages());

        //Deletes all records
        Configure::write('MeCms.sitemap.pages', true);
        $Table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::pages());
    }

    /**
     * @test
     * @uses \MeCms\Utility\Sitemap\Sitemap::posts()
     */
    public function testPosts(): void
    {
        /** @var \MeCms\Model\Table\PostsCategoriesTable $Table */
        $Table = $this->getTable('MeCms.PostsCategories');

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
        $this->assertSame($expected, Sitemap::posts());
        $this->assertSame($expected, Cache::read('sitemap', $Table->getCacheName()));

        Configure::write('MeCms.sitemap.posts', false);
        $this->assertEmpty(Sitemap::posts());

        //Deletes all records
        Configure::write('MeCms.sitemap.posts', true);
        $Table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::posts());
    }

    /**
     * @test
     * @uses \MeCms\Utility\Sitemap\Sitemap::postsTags()
     */
    public function testPostsTags(): void
    {
        /** @var \MeCms\Model\Table\TagsTable $Table */
        $Table = $this->getTable('MeCms.Tags');

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
        $this->assertSame($expected, Sitemap::postsTags());
        $this->assertSame($expected, Cache::read('sitemap', $Table->getCacheName()));

        Configure::write('MeCms.sitemap.posts_tags', false);
        $this->assertEmpty(Sitemap::postsTags());

        //Deletes all records
        Configure::write('MeCms.sitemap.posts_tags', true);
        $Table->deleteAll(['id IS NOT' => null]);
        $this->assertEmpty(Sitemap::postsTags());
    }

    /**
     * @test
     * @uses \MeCms\Utility\Sitemap\Sitemap::staticPages()
     */
    public function testStaticPages(): void
    {
        $result = Sitemap::staticPages();

        //Checks here `lastmod` and `priority` values
        foreach ($result as $row) {
            $this->assertSame($row['lastmod'], (new FrozenTime($row['lastmod']))->format('c'));
            $this->assertSame('0.5', $row['priority']);
        }

        $expected = [
            'http://localhost/page/example-page-it',
            'http://localhost/page/page-from-app',
            'http://localhost/page/example-page',
            'http://localhost/page/test-from-plugin',
            'http://localhost/page/first-folder/page-on-first-from-plugin',
            'http://localhost/page/first-folder/second_folder/page_on_second_from_plugin',
        ];
        $this->assertSame($expected, array_map(fn(array $row) => $row['loc'], $result));

        Configure::write('MeCms.sitemap.static_pages', false);
        $this->assertEmpty(Sitemap::staticPages());
        Configure::write('MeCms.sitemap.static_pages', true);
    }

    /**
     * @test
     * @uses \MeCms\Utility\Sitemap\Sitemap::systems()
     */
    public function testSystems(): void
    {
        $this->assertSame([[
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
