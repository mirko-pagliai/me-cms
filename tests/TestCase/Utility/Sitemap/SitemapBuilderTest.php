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

use Cake\Utility\Hash;
use Cake\Utility\Xml;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\Sitemap\SitemapBuilder;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * SitemapTest class
 */
class SitemapBuilderTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var \MeCms\Utility\Sitemap\SitemapBuilder
     */
    protected SitemapBuilder $SitemapBuilder;

    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.Tags',
    ];

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->SitemapBuilder ??= new SitemapBuilder();
    }

    /**
     * Test for `getMethods()` method
     * @test
     */
    public function testGetMethods(): void
    {
        $expected = [
            [
                'class' => 'MeCms\Utility\Sitemap\Sitemap',
                'name' => 'pages',
            ],
            [
                'class' => 'MeCms\Utility\Sitemap\Sitemap',
                'name' => 'posts',
            ],
            [
                'class' => 'MeCms\Utility\Sitemap\Sitemap',
                'name' => 'postsTags',
            ],
            [
                'class' => 'MeCms\Utility\Sitemap\Sitemap',
                'name' => 'staticPages',
            ],
            [
                'class' => 'MeCms\Utility\Sitemap\Sitemap',
                'name' => 'systems',
            ],
        ];
        $this->assertSame($expected, $this->SitemapBuilder->getMethods('MeCms'));

        $expected = [
            [
                'class' => 'TestPlugin\Utility\Sitemap\Sitemap',
                'name' => 'urlMethod1',
            ],
            [
                'class' => 'TestPlugin\Utility\Sitemap\Sitemap',
                'name' => 'urlMethod2',
            ],
        ];
        $this->loadPlugins(['TestPlugin' => []]);
        $this->assertSame($expected, $this->SitemapBuilder->getMethods('TestPlugin'));

        //This plugin does not have the `Sitemap` class
        $this->loadPlugins(['TestPluginTwo' => []]);
        $this->assertEmpty($this->SitemapBuilder->getMethods('TestPluginTwo'));
    }

    /**
     * Test for `generate()` method
     * @test
     */
    public function testGenerate(): void
    {
        $map = Xml::toArray(Xml::build($this->SitemapBuilder->generate()))['urlset']['url'];
        $this->assertNotEmpty($map);
        $this->assertSame(['loc' => 'http://localhost/', 'priority' => '0.5'], array_value_first($map));
        $this->assertNotEmpty(Hash::extract($map, '{n}.loc'));
        $this->assertNotEmpty(Hash::extract($map, '{n}.priority'));
    }

    /**
     * Test for `generate()` method, with a plugin
     * @test
     */
    public function testGenerateWithPlugin(): void
    {
        $this->loadPlugins(['TestPlugin' => []]);
        $map = $this->SitemapBuilder->generate();
        $this->assertStringContainsString('first-folder/page-on-first-from-plugin', $map);
        $this->assertStringContainsString('first-folder/second_folder/page_on_second_from_plugin', $map);
        $this->assertStringContainsString('test-from-plugin', $map);
        $this->assertNotEmpty(Xml::toArray(Xml::build($map))['urlset']['url']);
    }
}
