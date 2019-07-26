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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\I18n\I18n;
use MeCms\TestSuite\ControllerTestCase;

/**
 * SystemsControllerTest class
 */
class SystemsControllerTest extends ControllerTestCase
{
    /**
     * Asserts that the cache is empty.
     *
     * If uses the same cache keys used by `createSomeTemporaryData()`.
     * @see createSomeTemporaryData()
     * @return void
     */
    public function assertCacheIsEmpty(): void
    {
        array_map([$this, 'assertNull'], [Cache::read('value'), Cache::read('varFromGroup', 'posts')]);
    }

    /**
     * Internal method to create some temporary data (cache, assets, logs,
     *  sitemap, thumbnails)
     * @return array Files
     */
    protected function createSomeTemporaryData(): array
    {
        //Writes some cache data
        Cache::write('value', 'data');
        Cache::write('valueFromGroup', 'data', 'posts');

        $files = [
            'assets' => getConfigOrFail('Assets.target') . DS . 'asset_file',
            'logs' => LOGS . 'log_file',
            'sitemap' => SITEMAP,
            'thumbs' => getConfigOrFail('Thumber.target') . DS . md5('a') . '_' . md5('a') . '.jpg',
        ];

        foreach ($files as $file) {
            @create_file($file, str_repeat('a', 255));
        }

        return $files;
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        create_kcfinder_files();
        I18n::setLocale('en_US');

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        Cache::clearAll();

        //Deletes all temporary files
        @unlink_recursive(getConfigOrFail('Assets.target'));
        @unlink_recursive(getConfigOrFail('Thumber.target'));
        @unlink(SITEMAP);

        parent::tearDown();
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        parent::testIsAuthorized();

        //With `tmpCleaner` action
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ], 'tmpCleaner');

        foreach (['all', 'logs'] as $param) {
            $this->Controller->request = $this->Controller->getRequest()->withParam('pass.0', $param);
            $this->assertGroupsAreAuthorized([
                'admin' => true,
                'manager' => false,
                'user' => false,
            ]);
        }
    }

    /**
     * Tests for `browser()` method
     * @test
     */
    public function testBrowser()
    {
        @mkdir(UPLOADED . 'docs');

        $url = $this->url + ['action' => 'browser'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'browser.php');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $this->viewVariable('types'));
        $this->assertEmpty($this->viewVariable('kcfinder'));

        //GET request. Asks for `docs` type
        $this->get($url + ['?' => ['type' => 'docs']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'browser.php');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $this->viewVariable('types'));
        $this->assertStringContainsString('kcfinder/browse.php?lang=en&type=docs', $this->viewVariable('kcfinder'));

        //GET request. Now with `it` locale
        I18n::setLocale('it');
        $this->get($url + ['?' => ['type' => 'docs']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'browser.php');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $this->viewVariable('types'));
        $this->assertStringContainsString('kcfinder/browse.php?lang=it&type=docs', $this->viewVariable('kcfinder'));

        //GET request. Now only the `images` type exists
        @rmdir(UPLOADED . 'docs');
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'browser.php');
        $this->assertEquals(['images' => 'images'], $this->viewVariable('types'));
        $this->assertStringContainsString('kcfinder/browse.php?lang=it&type=images', $this->viewVariable('kcfinder'));
    }

    /**
     * Tests for `changelogs()` method
     * @test
     */
    public function testChangelogs()
    {
        $url = $this->url + ['action' => 'changelogs'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'changelogs.php');
        $this->assertNotEmpty($this->viewVariable('files'));
        $this->assertIsArray($this->viewVariable('files'));
        $this->assertEmpty($this->viewVariable('changelog'));

        //GET request. Asks for a changelog file
        $this->get($url + ['?' => ['file' => 'mecms']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'changelogs.php');
        $this->assertNotEmpty($this->viewVariable('changelog'));
        $this->assertTrue(is_html($this->viewVariable('changelog')));
    }

    /**
     * Tests for `checkup()` method
     * @test
     */
    public function testCheckup()
    {
        $expectedViewVars = [
            'webroot',
            'temporary',
            'plugins',
            'phpExtensions',
            'kcfinder',
            'cakephp',
            'cache',
            'backups',
            'apache',
        ];
        $this->get($this->url + ['action' => 'checkup']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'checkup.php');
        array_map([$this, 'assertNotEmpty'], array_map([$this, 'viewVariable'], $expectedViewVars));
    }

    /**
     * Tests for `clearSitemap()` method
     * @test
     */
    public function testClearSitemap()
    {
        @unlink(SITEMAP);
        $this->assertTrue($this->invokeMethod($this->Controller, 'clearSitemap'));

        $this->createSomeTemporaryData();
        $this->assertTrue($this->invokeMethod($this->Controller, 'clearSitemap'));
        $this->assertFileNotExists(SITEMAP);
    }

    /**
     * Tests for `tmpCleaner()` method
     * @test
     */
    public function testTmpCleaner()
    {
        $url = $this->url + ['action' => 'tmpCleaner'];

        //POST request. Cleans all
        $files = $this->createSomeTemporaryData();
        $this->post($url + ['all']);
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertCacheIsEmpty();
        array_map([$this, 'assertFileNotExists'], $files);

        //POST request. Cleans the cache
        $files = $this->createSomeTemporaryData();
        $this->post($url + ['cache']);
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertCacheIsEmpty();

        //POST request. Cleans assets, logs, sitemap and thumbs
        foreach (['assets', 'logs', 'sitemap', 'thumbs'] as $tmpName) {
            $this->post($url + [$tmpName]);
            $this->assertRedirect(['action' => 'tmpViewer']);
            $this->assertFlashMessage(I18N_OPERATION_OK);
            $this->assertFileNotExists($files[$tmpName]);
        }

        //POST request. Invalid type
        $this->post($url + ['invalidType']);
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);

        //GET request
        $this->get($url + ['all']);
        $this->assertResponseError();
    }

    /**
     * Tests for `tmpViewer()` method
     * @test
     */
    public function testTmpViewer()
    {
        $expectedViewVars = [
            'assetsSize',
            'cacheSize',
            'logsSize',
            'sitemapSize',
            'thumbsSize',
            'totalSize',
            'cacheStatus',
        ];
        $this->createSomeTemporaryData();
        $this->get($this->url + ['action' => 'tmpViewer']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'tmp_viewer.php');
        array_map([$this, 'assertNotEmpty'], array_map([$this, 'viewVariable'], $expectedViewVars));
    }
}
