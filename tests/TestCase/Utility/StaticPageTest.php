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
namespace MeCms\Test\TestCase\Utility;

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Core\Plugin;
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

        $this->StaticPage = new StaticPage();
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
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $pages = $this->StaticPage->all();
        $this->assertContainsOnlyInstancesOf(Entity::class, $pages);
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
        $TestPluginPath = rtr(array_value_first(App::path('Template', 'TestPlugin')));
        $this->assertEquals([
            'tests' . DS . 'test_app' . DS . 'TestApp' . DS . 'Template' . DS . 'StaticPages' . DS . 'page-from-app.ctp',
            'src' . DS . 'Template' . DS . 'StaticPages' . DS . 'cookies-policy-it.ctp',
            'src' . DS . 'Template' . DS . 'StaticPages' . DS . 'cookies-policy.ctp',
            $TestPluginPath . 'StaticPages' . DS . 'test-from-plugin.ctp',
            $TestPluginPath . 'StaticPages' . DS . 'first-folder' . DS . 'page-on-first-from-plugin.ctp',
            $TestPluginPath . 'StaticPages' . DS . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin.ctp',
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
            DS . 'StaticPages' . DS . 'page-from-app',
            'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy-it',
            'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'test-from-plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'page-on-first-from-plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin',
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
        $expected = 'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy';
        $this->assertEquals($expected, $this->StaticPage->get('cookies-policy'));

        $originalDefaultlLocale = ini_set('intl.default_locale', 'it_IT');
        $this->assertEquals(sprintf('%s-it', $expected), $this->StaticPage->get('cookies-policy'));
        ini_set('intl.default_locale', $originalDefaultlLocale);

        $originalDefaultlLocale = ini_set('intl.default_locale', 'it');
        $this->assertEquals(sprintf('%s-it', $expected), $this->StaticPage->get('cookies-policy'));
        ini_set('intl.default_locale', $originalDefaultlLocale);
    }

    /**
     * Test for `getAllPaths()` method
     * @test
     */
    public function testGetAllPaths()
    {
        $paths = $this->invokeMethod($this->StaticPage, 'getAllPaths');
        $this->assertEquals([
            APP . 'Template' . DS . 'StaticPages' . DS,
            ROOT . 'src' . DS . 'Template' . DS . 'StaticPages' . DS,
            Plugin::path('TestPlugin') . 'src' . DS . 'Template' . DS . 'StaticPages' . DS,
        ], $paths);
        $this->assertEquals(Cache::read('paths', 'static_pages'), $paths);
    }

    /**
     * Test for `getSlug()` method
     * @group onlyUnix
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
        ] as $file) {
            $this->assertEquals('my-file', $getSlugMethod($file, '/first/second'));
            $this->assertEquals('my-file', $getSlugMethod($file, '/first/second/'));
        }

        $this->assertEquals('first/my-file', $getSlugMethod('first/my-file.ctp', '/first/second'));
        $this->assertEquals('third/my-file', $getSlugMethod('/first/second/third/my-file.ctp', '/first/second'));
    }

    /**
     * Test for `getSlug()` method on Windows
     * @group onlyWindows
     * @test
     */
    public function testGetSlugWin()
    {
        $getSlugMethod = function () {
            return $this->invokeMethod($this->StaticPage, 'getSlug', func_get_args());
        };

        $this->assertEquals('my-file', $getSlugMethod('\\first\\second\\my-file.ctp', '\\first\\second'));
        $this->assertEquals('my-file', $getSlugMethod('\\first\\second\\my-file.ctp', '\\first\\second\\'));
        $this->assertEquals('my-file', $getSlugMethod('C:\\\\first\\my-file.ctp', 'C:\\\\first'));
        $this->assertEquals('second/my-file', $getSlugMethod('\\first\\second\\my-file.ctp', '\\first'));
        $this->assertEquals('second/my-file', $getSlugMethod('\\first\\second\\my-file.ctp', '\\first\\'));
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
