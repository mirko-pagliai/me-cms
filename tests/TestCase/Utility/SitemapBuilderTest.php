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
use Cake\I18n\Time;
use Cake\Utility\Hash;
use Cake\Utility\Xml;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\SitemapBuilder;
use MeTools\TestSuite\IntegrationTestTrait;

/**
 * SitemapTest class
 */
class SitemapBuilderTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var \MeCms\Utility\SitemapBuilder
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
        'plugin.MeCms.Photos',
        'plugin.MeCms.PhotosAlbums',
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
        'plugin.MeCms.Tags',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->SitemapBuilder = new SitemapBuilder;
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Cache::clearAll();
    }

    /**
     * Test for `getMethods()` method
     * @test
     */
    public function testGetMethods()
    {
        $extractNamesFromMethods = function ($methods) {
            return collection($methods)->extract('name')->toArray();
        };

        $methods = $this->invokeMethod($this->SitemapBuilder, 'getMethods', ['MeCms']);
        $this->assertEquals([
            'pages',
            'photos',
            'posts',
            'postsTags',
            'staticPages',
            'systems',
        ], $extractNamesFromMethods($methods));

        $this->loadPlugins(['TestPlugin']);
        $methods = $this->invokeMethod($this->SitemapBuilder, 'getMethods', ['TestPlugin']);
        $this->assertEquals(['urlMethod1', 'urlMethod2'], $extractNamesFromMethods($methods));

        //This plugin does not have the `Sitemap` class
        $this->loadPlugins(['TestPluginTwo']);
        $methods = $this->invokeMethod($this->SitemapBuilder, 'getMethods', ['TestPluginTwo']);
        $this->assertEquals([], $methods);
    }

    /**
     * Test for `parse()` method
     * @test
     */
    public function testParse()
    {
        $parseMethod = function ($url, array $options = []) {
            return $this->invokeMethod($this->SitemapBuilder, 'parse', [$url, $options]);
        };

        $expected = ['loc' => 'http://localhost/', 'priority' => '0.5'];
        $this->assertEquals($expected, $parseMethod(['_name' => 'homepage']));
        $this->assertEquals($expected, $parseMethod('/'));

        $expected = [
            'loc' => 'http://localhost/',
            'lastmod' => '2014-01-10T11:11:00+00:00',
            'priority' => '0.5',
        ];
        $result = $parseMethod('/', ['lastmod' => new Time('2014-01-10 11:11')]);
        $this->assertEquals($expected, $result);

        $result = $parseMethod('/', ['lastmod' => new Time('2014-01-10T11:11:00+00:00')]);
        $this->assertEquals($expected, $result);

        $result = $parseMethod('/', ['lastmod' => '2014-01-10T11:11:00+00:00']);
        $this->assertEquals($expected, $result);

        $result = $parseMethod('/', ['priority' => '0.4']);
        $this->assertEquals(['loc' => 'http://localhost/', 'priority' => '0.4'], $result);
    }

    /**
     * Test for `generate()` method
     * @test
     */
    public function testGenerate()
    {
        $this->loadFixtures();
        $map = $this->SitemapBuilder->generate();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $map);
        $this->assertStringEndsWith('  </url>' . PHP_EOL . '</urlset>', $map);

        $mapAsArray = Xml::toArray(Xml::build($map))['urlset']['url'];
        $this->assertNotEmpty($mapAsArray);
        $this->assertNotEmpty(Hash::extract($mapAsArray, '{n}.loc'));
        $this->assertNotEmpty(Hash::extract($mapAsArray, '{n}.priority'));
    }

    /**
     * Test for `generate()` method, with a plugin
     * @test
     */
    public function testGenerateWithPlugin()
    {
        $this->loadFixtures();
        $this->loadPlugins(['TestPlugin']);
        $map = $this->SitemapBuilder->generate();
        $this->assertContains('first-folder/page-on-first-from-plugin', $map);
        $this->assertContains('first-folder/second_folder/page_on_second_from_plugin', $map);
        $this->assertContains('test-from-plugin', $map);
        $this->assertNotEmpty(Xml::toArray(Xml::build($map))['urlset']['url']);
    }
}
