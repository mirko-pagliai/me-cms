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
    protected $SitemapBuilder;

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
        'plugin.MeCms.Tags',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->SitemapBuilder = $this->SitemapBuilder ?: new SitemapBuilder();
    }

    /**
     * Test for `getMethods()` method
     * @test
     */
    public function testGetMethods(): void
    {
        $methods = $this->SitemapBuilder->getMethods('MeCms');
        $this->assertEquals([
            'pages',
            'posts',
            'postsTags',
            'staticPages',
            'systems',
        ], $methods->extract('name')->toArray());

        $this->loadPlugins(['TestPlugin']);
        $methods = $this->SitemapBuilder->getMethods('TestPlugin');
        $this->assertEquals(['urlMethod1', 'urlMethod2'], $methods->extract('name')->toArray());

        //This plugin does not have the `Sitemap` class
        $this->loadPlugins(['TestPluginTwo']);
        $methods = $this->SitemapBuilder->getMethods('TestPluginTwo');
        $this->assertCount(0, $methods);
    }

    /**
     * Test for `generate()` method
     * @test
     */
    public function testGenerate(): void
    {
        $this->loadFixtures();
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
        $this->loadFixtures();
        $this->loadPlugins(['TestPlugin']);
        $map = $this->SitemapBuilder->generate();
        $this->assertStringContainsString('first-folder/page-on-first-from-plugin', $map);
        $this->assertStringContainsString('first-folder/second_folder/page_on_second_from_plugin', $map);
        $this->assertStringContainsString('test-from-plugin', $map);
        $this->assertNotEmpty(Xml::toArray(Xml::build($map))['urlset']['url']);
    }
}
