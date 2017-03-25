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
use Cake\TestSuite\TestCase;
use MeCms\Core\Plugin;
use MeCms\Utility\StaticPage;
use Reflection\ReflectionTrait;

/**
 * StaticPageTest class
 */
class StaticPageTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Utility\StaticPage
     */
    protected $StaticPage;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Plugin::load('TestPlugin');

        $this->StaticPage = new StaticPage;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        ini_set('intl.default_locale', 'en_US');

        Plugin::unload('TestPlugin');

        unset($this->StaticPage);
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $pages = $this->StaticPage->all();

        //Checks filenames
        $filenames = collection($pages)->extract(function ($page) {
            return $page->filename;
        })->toArray();

        $this->assertEquals([
            'cookies-policy-it',
            'cookies-policy',
            'test-from-plugin',
            'page-on-first-from-plugin',
            'page_on_second_from_plugin',
        ], $filenames);

        //Checks paths
        $paths = collection($pages)->extract(function ($page) {
            return $page->path;
        })->toList();

        $this->assertEquals([
            'src/Template/StaticPages/cookies-policy-it.ctp',
            'src/Template/StaticPages/cookies-policy.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/test-from-plugin.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/page-on-first-from-plugin.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/second_folder/page_on_second_from_plugin.ctp',
        ], $paths);

        //Checks slugs
        $slugs = collection($pages)->extract(function ($page) {
            return $page->slug;
        })->toList();

        $this->assertEquals([
            'cookies-policy-it',
            'cookies-policy',
            'test-from-plugin',
            'first-folder/page-on-first-from-plugin',
            'first-folder/second_folder/page_on_second_from_plugin',
        ], $slugs);

        //Checks titles
        $titles = collection($pages)->extract(function ($page) {
            return $page->title;
        })->toList();

        $this->assertEquals([
            'Cookies Policy It',
            'Cookies Policy',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ], $titles);

        //Checks modified times
        foreach ($pages as $page) {
            $this->assertInstanceOf('Cake\I18n\FrozenTime', $page->modified);
        }
    }

    /**
     * Test for `get()` method
     * @test
     */
    public function testGet()
    {
        //Gets all slugs from pages
        $slugs = collection($this->StaticPage->all())->map(function ($page) {
            return $page->slug;
        })->toList();

        //Now, on the contrary, gets all pages from slugs
        $pages = collection($slugs)->map(function ($slug) {
            return $this->StaticPage->get($slug);
        })->toList();

        $this->assertEquals([
            'MeCms.StaticPages/cookies-policy-it',
            'MeCms.StaticPages/cookies-policy',
            'TestPlugin.StaticPages/test-from-plugin',
            'TestPlugin.StaticPages/first-folder/page-on-first-from-plugin',
            'TestPlugin.StaticPages/first-folder/second_folder/page_on_second_from_plugin',
        ], $pages);

        //Tries to get a no existing page
        $this->assertFalse($this->StaticPage->get('no-Existing'));
    }

    /**
     * Test for `get()` method, using a different locale
     * @test
     */
    public function testGetDifferentLocale()
    {
        $this->assertEquals('MeCms.StaticPages/cookies-policy', $this->StaticPage->get('cookies-policy'));

        ini_set('intl.default_locale', 'it');

        $this->assertEquals('MeCms.StaticPages/cookies-policy-it', $this->StaticPage->get('cookies-policy'));
    }

    /**
     * Test for `paths()` method
     * @test
     */
    public function testPaths()
    {
        $paths = $this->invokeMethod($this->StaticPage, 'paths');

        $this->assertEquals(Cache::read('paths', 'static_pages'), $paths);

        //Gets relative paths
        $paths = collection($paths)->extract(function ($path) {
            return rtr($path);
        })->toList();

        $this->assertEquals([
            'tests/test_app/TestApp/Template/StaticPages',
            'src/Template/StaticPages',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages',
        ], $paths);
    }

    /**
     * Test for `slug()` method
     * @test
     */
    public function testSlug()
    {
        $files = [
            'my-file',
            'my-file.ctp',
            '/first/second/my-file.ctp',
            '/first/second/my-file.php',
        ];

        foreach ($files as $file) {
            $this->assertEquals('my-file', $this->invokeMethod($this->StaticPage, 'slug', [$file, '/first/second']));
            $this->assertEquals('my-file', $this->invokeMethod($this->StaticPage, 'slug', [$file, '/first/second/']));
        }

        $result = $this->invokeMethod($this->StaticPage, 'slug', ['first/my-file.ctp', '/first/second']);
        $this->assertEquals('first/my-file', $result);

        $result = $this->invokeMethod($this->StaticPage, 'slug', ['/first/second/third/my-file.ctp', '/first/second']);
        $this->assertEquals('third/my-file', $result);
    }

    /**
     * Test for `title()` method
     * @test
     */
    public function testTitle()
    {
        $expected = [
            'Cookies Policy It',
            'Cookies Policy',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ];

        //Gets all slugs from pages
        $slugs = collection($this->StaticPage->all())->map(function ($page) {
            return $page->slug;
        })->toList();

        //Now gets all title from slugs
        $titles = collection($slugs)->map(function ($slug) {
            return $this->StaticPage->title($slug);
        })->toList();

        $this->assertEquals($expected, $titles);

        //Gets all paths from pages
        $paths = collection($this->StaticPage->all())->map(function ($page) {
            return $page->path;
        })->toList();

        //Now gets all title from paths
        $titles = collection($paths)->map(function ($path) {
            return $this->StaticPage->title($path);
        })->toList();

        $this->assertEquals($expected, $titles);
    }
}
