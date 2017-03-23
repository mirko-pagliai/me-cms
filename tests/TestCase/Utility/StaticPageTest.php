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
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Plugin::load('TestPlugin');
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
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $pages = StaticPage::all();

        //Checks filenames
        $filenames = collection($pages)->extract(function ($page) {
            return $page->filename;
        })->toArray();

        $this->assertEquals([
            'cookies-policy-it',
            'cookies-policy',
            'test',
            'page-on-first',
            'page_on_second',
        ], $filenames);

        //Checks paths
        $paths = collection($pages)->extract(function ($page) {
            return $page->path;
        })->toArray();

        $this->assertEquals([
            'src/Template/StaticPages/cookies-policy-it.ctp',
            'src/Template/StaticPages/cookies-policy.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/test.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/page-on-first.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/second_folder/page_on_second.ctp',
        ], $paths);

        //Checks slugs
        $slugs = collection($pages)->extract(function ($page) {
            return $page->slug;
        })->toArray();

        $this->assertEquals([
            'cookies-policy-it',
            'cookies-policy',
            'test',
            'first-folder/page-on-first',
            'first-folder/second_folder/page_on_second',
        ], $slugs);

        //Checks titles
        $titles = collection($pages)->extract(function ($page) {
            return $page->title;
        })->toArray();

        $this->assertEquals([
            'Cookies Policy It',
            'Cookies Policy',
            'Test',
            'Page On First',
            'Page On Second',
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
        $slugs = [
            'cookies-policy-it',
            'cookies-policy',
            'test',
            'first-folder/page-on-first',
            'first-folder/second_folder/page_on_second',
        ];

        $pages = array_map(function ($slug) {
            return StaticPage::get($slug);
        }, $slugs);

        $this->assertEquals([
            'MeCms.StaticPages/cookies-policy-it',
            'MeCms.StaticPages/cookies-policy',
            'TestPlugin.StaticPages/test',
            'TestPlugin.StaticPages/first-folder/page-on-first',
            'TestPlugin.StaticPages/first-folder/second_folder/page_on_second',
        ], $pages);

        //Tries to get a no existing page
        $this->assertFalse(StaticPage::get('no-Existing'));
    }

    /**
     * Test for `get()` method, using a different locale
     * @test
     */
    public function testGetDifferentLocale()
    {
        $this->assertEquals('MeCms.StaticPages/cookies-policy', StaticPage::get('cookies-policy'));

        ini_set('intl.default_locale', 'it');

        $this->assertEquals('MeCms.StaticPages/cookies-policy-it', StaticPage::get('cookies-policy'));
    }

    /**
     * Test for `paths()` method
     * @test
     */
    public function testPaths()
    {
        $object = new StaticPage;

        $paths = collection($this->invokeMethod($object, 'paths'))
            ->extract(function ($path) {
                return rtr($path);
            })
            ->toArray();

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
        $object = new StaticPage;

        $files = [
            'my-file',
            'my-file.ctp',
            '/first/second/my-file.ctp',
            '/first/second/my-file.php',
        ];

        foreach ($files as $file) {
            $this->assertEquals('my-file', $this->invokeMethod($object, 'slug', [$file, '/first/second']));
            $this->assertEquals('my-file', $this->invokeMethod($object, 'slug', [$file, '/first/second/']));
        }

        $result = $this->invokeMethod($object, 'slug', ['first/my-file.ctp', '/first/second']);
        $this->assertEquals('first/my-file', $result);

        $result = $this->invokeMethod($object, 'slug', ['/first/second/third/my-file.ctp', '/first/second']);
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
            'Test',
            'Page On First',
            'Page On Second',
        ];

        //Tries using slugs
        $slugs = [
            'cookies-policy-it',
            'cookies-policy',
            'test',
            'first-folder/page-on-first',
            'first-folder/second_folder/page_on_second',
        ];

        $titles = array_map(function ($slug) {
            return StaticPage::title($slug);
        }, $slugs);

        $this->assertEquals($expected, $titles);

        //Tries using paths
        $paths = [
            'src/Template/StaticPages/cookies-policy-it.ctp',
            'src/Template/StaticPages/cookies-policy.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/test.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/page-on-first.ctp',
            'tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/first-folder/second_folder/page_on_second.ctp',
        ];

        $titles = array_map(function ($path) {
            return StaticPage::title($path);
        }, $paths);

        $this->assertEquals($expected, $titles);
    }
}
