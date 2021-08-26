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
use Cake\Core\Plugin;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use MeCms\TestSuite\TestCase;
use MeCms\Utility\StaticPage;
use Tools\Filesystem;

/**
 * StaticPageTest class
 */
class StaticPageTest extends TestCase
{
    /**
     * Cache keys to clear for each test
     * @var array
     */
    protected $cacheToClear = ['static_pages'];

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->removePlugins(['TestPlugin']);
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll(): void
    {
        $this->loadPlugins(['TestPlugin']);
        $TestPluginPath = (new Filesystem())->rtr(Plugin::templatePath('TestPlugin')) . DS . 'StaticPages' . DS;

        $pages = StaticPage::all();
        $this->assertContainsOnlyInstancesOf(Entity::class, $pages);
        $this->assertContainsOnlyInstancesOf(FrozenTime::class, $pages->extract('modified'));

        //Checks filenames
        $this->assertEquals([
            'page-from-app',
            'cookies-policy-it',
            'cookies-policy',
            'page-on-first-from-plugin',
            'page_on_second_from_plugin',
            'test-from-plugin',
        ], $pages->extract('filename')->toArray());

        //Checks paths
        $this->assertEquals([
            'tests' . DS . 'test_app' . DS . 'TestApp' . DS . 'templates' . DS . 'StaticPages' . DS . 'page-from-app.' . StaticPage::EXTENSION,
            'templates' . DS . 'StaticPages' . DS . 'cookies-policy-it.' . StaticPage::EXTENSION,
            'templates' . DS . 'StaticPages' . DS . 'cookies-policy.' . StaticPage::EXTENSION,
            $TestPluginPath . 'first-folder' . DS . 'page-on-first-from-plugin.' . StaticPage::EXTENSION,
            $TestPluginPath . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin.' . StaticPage::EXTENSION,
            $TestPluginPath . 'test-from-plugin.' . StaticPage::EXTENSION,
        ], $pages->extract('path')->toArray());

        //Checks slugs
        $this->assertEquals([
            'page-from-app',
            'cookies-policy-it',
            'cookies-policy',
            'first-folder/page-on-first-from-plugin',
            'first-folder/second_folder/page_on_second_from_plugin',
            'test-from-plugin',
        ], $pages->extract('slug')->toArray());

        //Checks titles
        $this->assertEquals([
            'Page From App',
            'Cookies Policy It',
            'Cookies Policy',
            'Page On First From Plugin',
            'Page On Second From Plugin',
            'Test From Plugin',
        ], $pages->extract('title')->toArray());
    }

    /**
     * Test for `get()` method
     * @test
     */
    public function testGet(): void
    {
        $this->loadPlugins(['TestPlugin']);

        //Gets all pages from slugs
        $pages = array_map([StaticPage::class, 'get'], StaticPage::all()->extract('slug')->toArray());
        $this->assertEquals([
            DS . 'StaticPages' . DS . 'page-from-app',
            'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy-it',
            'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'page-on-first-from-plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'test-from-plugin',
        ], $pages);

        //Tries to get a no existing page
        $this->assertNull(StaticPage::get('no-Existing'));
    }

    /**
     * Test for `get()` method, using a different locale
     * @test
     */
    public function testGetDifferentLocale(): void
    {
        $expected = 'MeCms.' . DS . 'StaticPages' . DS . 'cookies-policy';
        $this->assertEquals($expected, StaticPage::get('cookies-policy'));

        $originalValue = ini_set('intl.default_locale', 'it_IT');
        $this->assertEquals(sprintf('%s-it', $expected), StaticPage::get('cookies-policy'));
        ini_set('intl.default_locale', (string)$originalValue);

        $originalValue = ini_set('intl.default_locale', 'it');
        $this->assertEquals(sprintf('%s-it', $expected), StaticPage::get('cookies-policy'));
        ini_set('intl.default_locale', (string)$originalValue);
    }

    /**
     * Test for `getPaths()` method
     * @test
     */
    public function testGetPaths(): void
    {
        $this->loadPlugins(['TestPlugin']);
        $result = StaticPage::getPaths();
        $this->assertSame([
            'App' => APP . 'templates' . DS . 'StaticPages',
            'MeCms' => ROOT . 'templates' . DS . 'StaticPages',
            'TestPlugin' => Plugin::templatePath('TestPlugin') . 'StaticPages',
        ], $result);
        $this->assertEquals(Cache::read('paths', 'static_pages'), $result);
    }

    /**
     * Test for `getSlug()` method
     * @requires OS Linux
     * @test
     */
    public function testGetSlug(): void
    {
        foreach (['my-file', '/first/second/my-file'] as $file) {
            $this->assertEquals('my-file', StaticPage::getSlug($file, '/first/second'));
            $this->assertEquals('my-file', StaticPage::getSlug($file, '/first/second/'));
            $this->assertEquals('my-file', StaticPage::getSlug($file . '.' . StaticPage::EXTENSION, '/first/second'));
        }

        $this->assertEquals('first/my-file', StaticPage::getSlug('first/my-file.' . StaticPage::EXTENSION, '/first/second'));
        $this->assertEquals('third/my-file', StaticPage::getSlug('/first/second/third/my-file.' . StaticPage::EXTENSION, '/first/second'));
    }

    /**
     * Test for `getSlug()` method on Windows
     * @requires OS WIN32|WINNT
     * @test
     */
    public function testGetSlugWin(): void
    {
        foreach ([
            '\\first\\second' => '\\first\\second\\my-file',
            '\\first\\second\\' => '\\first\\second\\my-file',
            'C:\\\\first' => 'C:\\\\first\\my-file',
        ] as $relativePath => $absolutePath) {
            $this->assertEquals('my-file', StaticPage::getSlug($absolutePath, $relativePath));
            $this->assertEquals('my-file', StaticPage::getSlug($absolutePath . '.' . StaticPage::EXTENSION, $relativePath));
        }

        $this->assertEquals('second/my-file', StaticPage::getSlug('\\first\\second\\my-file.' . StaticPage::EXTENSION, '\\first'));
        $this->assertEquals('second/my-file', StaticPage::getSlug('\\first\\second\\my-file.' . StaticPage::EXTENSION, '\\first\\'));
    }

    /**
     * Test for `getTitle()` method
     * @test
     */
    public function testGetTitle(): void
    {
        $expected = [
            'Page From App',
            'Cookies Policy It',
            'Cookies Policy',
            'Page On First From Plugin',
            'Page On Second From Plugin',
            'Test From Plugin',
        ];

        $getTitles = function (array $pathsOrSlugs): array {
            return array_map(function (string $pathOrSlug): string {
                return StaticPage::getTitle($pathOrSlug);
            }, $pathsOrSlugs);
        };

        $this->loadPlugins(['TestPlugin']);
        $this->assertSame($expected, $getTitles(StaticPage::all()->extract('path')->toArray()));
        $this->assertSame($expected, $getTitles(StaticPage::all()->extract('slug')->toArray()));
    }
}
