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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Utility;

use Cake\Cache\Cache;
use Cake\Core\Plugin;
use Cake\I18n\Time;
use Cake\TestSuite\TestCase;
use Cake\Utility\Xml;
use MeCms\Utility\SitemapBuilder;
use Reflection\ReflectionTrait;

/**
 * SitemapTest class
 */
class SitemapBuilderTest extends TestCase
{
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
        $this->loadFixtures('Pages', 'PagesCategories', 'Photos', 'PhotosAlbums');

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
        Plugin::load('TestPlugin');

        $map = SitemapBuilder::generate();

        $this->assertContains('first-folder/page-on-first-from-plugin', $map);
        $this->assertContains('first-folder/second_folder/page_on_second_from_plugin', $map);
        $this->assertContains('test-from-plugin', $map);

        $mapAsArray = Xml::toArray(Xml::build($map))['urlset']['url'];

        $this->assertGreaterThan(0, count($mapAsArray));
    }
}
