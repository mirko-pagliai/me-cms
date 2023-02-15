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
     * Called after every test method
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Cache::clear('static_pages');

        $this->removePlugins(['TestPlugin']);
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll(): void
    {
        $this->loadPlugins(['TestPlugin' => []]);
        $TestPluginPath = Filesystem::instance()->rtr(Plugin::templatePath('TestPlugin')) . DS . 'StaticPages' . DS;

        $pages = StaticPage::all();
        $this->assertContainsOnlyInstancesOf(Entity::class, $pages);
        $this->assertContainsOnlyInstancesOf(FrozenTime::class, $pages->extract('modified'));

        //Checks filenames
        $this->assertEqualsCanonicalizing([
            'example-page',
            'example-page-it',
            'page-from-app',
            'test-from-plugin',
            'page-on-first-from-plugin',
            'page_on_second_from_plugin',
        ], $pages->extract('filename')->toArray());

        //Checks paths
        $this->assertEqualsCanonicalizing([
            'tests' . DS . 'test_app' . DS . 'TestApp' . DS . 'templates' . DS . 'StaticPages' . DS . 'example-page-it.' . StaticPage::EXTENSION,
            'tests' . DS . 'test_app' . DS . 'TestApp' . DS . 'templates' . DS . 'StaticPages' . DS . 'page-from-app.' . StaticPage::EXTENSION,
            'tests' . DS . 'test_app' . DS . 'TestApp' . DS . 'templates' . DS . 'StaticPages' . DS . 'example-page.' . StaticPage::EXTENSION,
            $TestPluginPath . 'test-from-plugin.' . StaticPage::EXTENSION,
            $TestPluginPath . 'first-folder' . DS . 'page-on-first-from-plugin.' . StaticPage::EXTENSION,
            $TestPluginPath . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin.' . StaticPage::EXTENSION,

        ], $pages->extract('path')->toArray());

        //Checks slugs
        $this->assertEqualsCanonicalizing([
            'example-page',
            'example-page-it',
            'page-from-app',
            'test-from-plugin',
            'first-folder/page-on-first-from-plugin',
            'first-folder/second_folder/page_on_second_from_plugin',
        ], $pages->extract('slug')->toArray());

        //Checks titles
        $this->assertEqualsCanonicalizing([
            'Example Page',
            'Example Page It',
            'Page From App',
            'Test From Plugin',
            'Page On First From Plugin',
            'Page On Second From Plugin',
        ], $pages->extract('title')->toArray());
    }

    /**
     * Test for `get()` method
     * @test
     */
    public function testGet(): void
    {
        $this->loadPlugins(['TestPlugin' => []]);

        //Gets all pages from slugs
        $pages = array_map([StaticPage::class, 'get'], StaticPage::all()->extract('slug')->toArray());
        $this->assertEqualsCanonicalizing([
            DS . 'StaticPages' . DS . 'example-page',
            DS . 'StaticPages' . DS . 'example-page-it',
            DS . 'StaticPages' . DS . 'page-from-app',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'test-from-plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'page-on-first-from-plugin',
            'TestPlugin.' . DS . 'StaticPages' . DS . 'first-folder' . DS . 'second_folder' . DS . 'page_on_second_from_plugin',
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
        $expected = '/StaticPages/example-page';
        $this->assertSame($expected, StaticPage::get('example-page'));

        $originalValue = ini_set('intl.default_locale', 'it_IT');
        $expected = '/StaticPages/example-page-it';
        $this->assertSame($expected, StaticPage::get('example-page'));
        ini_set('intl.default_locale', (string)$originalValue);

        $originalValue = ini_set('intl.default_locale', 'it');
        $this->assertSame($expected, StaticPage::get('example-page'));
        ini_set('intl.default_locale', (string)$originalValue);
    }

    /**
     * Test for `getPaths()` method
     * @test
     */
    public function testGetPaths(): void
    {
        $this->loadPlugins(['TestPlugin' => []]);
        $result = StaticPage::getPaths();
        $this->assertSame([
            'App' => APP . 'templates' . DS . 'StaticPages',
            'MeCms' => ROOT . 'templates' . DS . 'StaticPages',
            'TestPlugin' => Plugin::templatePath('TestPlugin') . 'StaticPages',
        ], $result);
        $this->assertEquals(Cache::read('paths', 'static_pages'), $result);
    }
}
