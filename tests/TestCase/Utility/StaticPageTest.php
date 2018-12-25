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
use Cake\Core\App;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\StaticPage;

/**
 * StaticPageTest class
 */
class StaticPageTest extends TestCase
{
    /**
     * @var \MeCms\Utility\StaticPage
     */
    protected $StaticPage;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->StaticPage = new StaticPage;
        $this->loadPlugins(['TestPlugin']);
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
     * Test for `getAppPath()` method
     * @test
     */
    public function testGetAppPath()
    {
        $result = rtr($this->invokeMethod($this->StaticPage, 'getAppPath'));
        $this->assertEquals('tests/test_app/TestApp/Template/StaticPages/', $result);
    }

    /**
     * Test for `getPluginPath()` method
     * @test
     */
    public function testGetPluginPath()
    {
        $result = rtr($this->invokeMethod($this->StaticPage, 'getPluginPath', ['TestPlugin']));
        $this->assertEquals('tests/test_app/TestApp/Plugin/TestPlugin/src/Template/StaticPages/', $result);
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $pages = $this->StaticPage->all();
        $this->assertContainsInstanceOf(Entity::class, $pages);

        foreach ($pages as $page) {
            $this->assertInstanceOf(FrozenTime::class, $page->modified);
        }

        //Checks filenames
        $filenames = Hash::extract($pages, '{n}.filename');
        $this->assertEquals([
            'page-from-app',
            'cookies-policy-it',
            'cookies-policy',
            'test-from-plugin',
            'page-on-first-from-plugin',
            'page_on_second_from_plugin',
        ], $filenames);

        //Checks paths
        $paths = Hash::extract($pages, '{n}.path');
        $TestPluginPath = rtr(first_value(App::path('Template', 'TestPlugin')));
        $this->assertEquals([
            'tests/test_app/TestApp/Template/StaticPages/page-from-app.ctp',
            'src/Template/StaticPages/cookies-policy-it.ctp',
            'src/Template/StaticPages/cookies-policy.ctp',
            $TestPluginPath . 'StaticPages/test-from-plugin.ctp',
            $TestPluginPath . 'StaticPages/first-folder/page-on-first-from-plugin.ctp',
            $TestPluginPath . 'StaticPages/first-folder/second_folder/page_on_second_from_plugin.ctp',
        ], $paths);

        //Checks slugs
        $slugs = Hash::extract($pages, '{n}.slug');
        $this->assertEquals([
            'page-from-app',
            'cookies-policy-it',
            'cookies-policy',
            'test-from-plugin',
            'first-folder/page-on-first-from-plugin',
            'first-folder/second_folder/page_on_second_from_plugin',
        ], $slugs);

        //Checks titles
        $titles = Hash::extract($pages, '{n}.title');
        $this->assertEquals([
            'Page From App',
            'Cookies Policy It',
            'Cookies Policy',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ], $titles);
    }

    /**
     * Test for `get()` method
     * @test
     */
    public function testGet()
    {
        //Gets all pages from slugs
        $pages = array_map([$this->StaticPage, 'get'], Hash::extract($this->StaticPage->all(), '{n}.slug'));
        $this->assertEquals([
            '/StaticPages/page-from-app',
            'MeCms./StaticPages/cookies-policy-it',
            'MeCms./StaticPages/cookies-policy',
            'TestPlugin./StaticPages/test-from-plugin',
            'TestPlugin./StaticPages/first-folder/page-on-first-from-plugin',
            'TestPlugin./StaticPages/first-folder/second_folder/page_on_second_from_plugin',
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
        $this->assertEquals('MeCms./StaticPages/cookies-policy', $this->StaticPage->get('cookies-policy'));

        $originalDefaultlLocale = ini_set('intl.default_locale', 'it');
        $this->assertEquals('MeCms./StaticPages/cookies-policy-it', $this->StaticPage->get('cookies-policy'));
        ini_set('intl.default_locale', $originalDefaultlLocale);
    }

    /**
     * Test for `getAllPaths()` method
     * @test
     */
    public function testGetAllPaths()
    {
        $paths = $this->invokeMethod($this->StaticPage, 'getAllPaths');
        $this->assertEquals(Cache::read('paths', 'static_pages'), $paths);

        //Checks relative paths
        $paths = array_map('rtr', $paths);
        $TestPluginPath = rtr(first_value(App::path('Template', 'TestPlugin')));
        $this->assertEquals([
            'tests/test_app/TestApp/Template/StaticPages/',
            'src/Template/StaticPages/',
            $TestPluginPath . 'StaticPages/',
        ], $paths);
    }

    /**
     * Test for `getSlug()` method
     * @test
     */
    public function testGetSlug()
    {
        $getSlugMethod = function () {
            return $this->invokeMethod($this->StaticPage, 'getSlug', func_get_args());
        };

        foreach ([
            'my-file',
            'my-file.ctp',
            '/first/second/my-file.ctp',
            '/first/second/my-file.php',
        ] as $file) {
            $this->assertEquals('my-file', $getSlugMethod($file, '/first/second'));
            $this->assertEquals('my-file', $getSlugMethod($file, '/first/second/'));
        }

        $this->assertEquals('first/my-file', $getSlugMethod('first/my-file.ctp', '/first/second'));
        $this->assertEquals('third/my-file', $getSlugMethod('/first/second/third/my-file.ctp', '/first/second'));
    }

    /**
     * Test for `title()` method
     * @test
     */
    public function testTitle()
    {
        $expected = [
            'Page From App',
            'Cookies Policy It',
            'Cookies Policy',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ];

        //Gets all slugs and all paths from pages
        $slugs = Hash::extract($this->StaticPage->all(), '{n}.slug');
        $paths = Hash::extract($this->StaticPage->all(), '{n}.path');

        $count = count($slugs);
        for ($id = 0; $id < $count; $id++) {
            $this->assertEquals($expected[$id], $this->StaticPage->title($slugs[$id]));
            $this->assertEquals($expected[$id], $this->StaticPage->title($paths[$id]));
        }
    }
}
