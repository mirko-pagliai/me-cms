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
use Cake\Core\Plugin;
use Cake\I18n\Time;
use Cake\TestSuite\TestCase;
use Cake\Utility\Xml;
use MeCms\Utility\SitemapBuilder;
use MeTools\TestSuite\Traits\LoadAllFixturesTrait;
use Reflection\ReflectionTrait;

/**
 * SitemapTest class
 */
class SitemapBuilderTest extends TestCase
{
    use LoadAllFixturesTrait;
    use ReflectionTrait;

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
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
        'plugin.me_cms.photos',
        'plugin.me_cms.photos_albums',
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
        'plugin.me_cms.tags',
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
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload('TestPlugin');
        Plugin::unload('TestPluginTwo');
    }

    /**
     * Test for `_getMethods()` method
     * @test
     */
    public function testGetMethods()
    {
        $object = new SitemapBuilder;

        $methods = $this->invokeMethod($object, '_getMethods', [ME_CMS]);
        $this->assertEquals([
            [
                'class' => '\MeCms\Utility\Sitemap',
                'name' => 'pages',
            ],
            [
                'class' => '\MeCms\Utility\Sitemap',
                'name' => 'photos',
            ],
            [
                'class' => '\MeCms\Utility\Sitemap',
                'name' => 'posts',
            ],
            [
                'class' => '\MeCms\Utility\Sitemap',
                'name' => 'postsTags',
            ],
            [
                'class' => '\MeCms\Utility\Sitemap',
                'name' => 'staticPages',
            ],
            [
                'class' => '\MeCms\Utility\Sitemap',
                'name' => 'systems',
            ],
        ], $methods);

        Plugin::load('TestPlugin');

        $methods = $this->invokeMethod($object, '_getMethods', ['TestPlugin']);
        $this->assertEquals([
            [
                'class' => '\TestPlugin\Utility\Sitemap',
                'name' => 'urlMethod1',
            ],
            [
                'class' => '\TestPlugin\Utility\Sitemap',
                'name' => 'urlMethod2',
            ],
        ], $methods);

        //This plugin does not have the `Sitemap` class
        Plugin::load('TestPluginTwo');

        $methods = $this->invokeMethod($object, '_getMethods', ['TestPluginTwo']);
        $this->assertEquals([], $methods);
    }

    /**
     * Test for `parse()` method
     * @test
     */
    public function testParse()
    {
        $object = new SitemapBuilder;

        $expected = [
            'loc' => 'http://localhost/',
            'priority' => '0.5',
        ];

        $parsed = $this->invokeMethod($object, 'parse', [['_name' => 'homepage']]);
        $this->assertEquals($expected, $parsed);

        $parsed = $this->invokeMethod($object, 'parse', ['/']);
        $this->assertEquals($expected, $parsed);

        $expected = [
            'loc' => 'http://localhost/',
            'lastmod' => '2014-01-10T11:11:00+00:00',
            'priority' => '0.5',
        ];

        $parsed = $this->invokeMethod($object, 'parse', ['/', ['lastmod' => (new Time('2014-01-10 11:11'))]]);
        $this->assertEquals($expected, $parsed);

        $parsed = $this->invokeMethod($object, 'parse', ['/', ['lastmod' => (new Time('2014-01-10T11:11:00+00:00'))]]);
        $this->assertEquals($expected, $parsed);

        $parsed = $this->invokeMethod($object, 'parse', ['/', ['lastmod' => '2014-01-10T11:11:00+00:00']]);
        $this->assertEquals($expected, $parsed);

        $parsed = $this->invokeMethod($object, 'parse', ['/', ['priority' => '0.4']]);
        $this->assertEquals([
            'loc' => 'http://localhost/',
            'priority' => '0.4',
        ], $parsed);
    }

    /**
     * Test for `generate()` method
     * @test
     */
    public function testGenerate()
    {
        $this->loadAllFixtures();

        $map = SitemapBuilder::generate();

        $this->assertStringStartsWith(
            '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL .
            '  <url>',
            $map
        );
        $this->assertStringEndsWith('  </url>' . PHP_EOL . '</urlset>', $map);

        $mapAsArray = Xml::toArray(Xml::build($map))['urlset']['url'];

        $this->assertGreaterThan(0, count($mapAsArray));

        foreach ($mapAsArray as $url) {
            $this->assertNotEmpty($url['loc']);
            $this->assertNotEmpty($url['priority']);
        }
    }

    /**
     * Test for `generate()` method, with a plugin
     * @test
     */
    public function testGenerateWithPlugin()
    {
        $this->loadAllFixtures();

        Plugin::load('TestPlugin');

        $map = SitemapBuilder::generate();

        $this->assertContains('first-folder/page-on-first-from-plugin', $map);
        $this->assertContains('first-folder/second_folder/page_on_second_from_plugin', $map);
        $this->assertContains('test-from-plugin', $map);

        $mapAsArray = Xml::toArray(Xml::build($map))['urlset']['url'];

        $this->assertGreaterThan(0, count($mapAsArray));
    }
}
