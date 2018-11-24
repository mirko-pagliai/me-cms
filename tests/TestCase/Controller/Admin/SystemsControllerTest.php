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
     */
    public function assertCacheIsEmpty()
    {
        $this->assertFalse(Cache::read('value'));
        $this->assertFalse(Cache::read('valueFromGroup', 'posts'));
    }

    /**
     * Internal method to create some temporary data (cache, assets, logs,
     *  sitemap, thumbnails)
     * @return array Files
     */
    protected function createSomeTemporaryData()
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
            file_put_contents($file, str_repeat('a', 255));
        }

        return $files;
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        I18n::setLocale('en_US');

        Cache::clearAll();

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        //Deletes all temporary files
        safe_unlink_recursive(getConfigOrFail('Assets.target'));
        safe_unlink_recursive(getConfigOrFail('Thumber.target'));
        safe_unlink(SITEMAP);

        parent::tearDown();
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->assertHasComponent('KcFinder', 'browser');
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
            $this->Controller->request = $this->Controller->request->withParam('pass.0', $param);
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
        safe_mkdir(UPLOADED . 'docs');

        $url = $this->url + ['action' => 'browser'];

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/browser.ctp');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $this->viewVariable('types'));
        $this->assertEmpty($this->viewVariable('kcfinder'));

        //GET request. Asks for `docs` type
        $this->get($url + ['?' => ['type' => 'docs']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/browser.ctp');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $this->viewVariable('types'));
        $this->assertContains('kcfinder/browse.php?lang=en&type=docs', $this->viewVariable('kcfinder'));

        //GET request. Now with `it` locale
        I18n::setLocale('it');
        $this->get($url + ['?' => ['type' => 'docs']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/browser.ctp');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $this->viewVariable('types'));
        $this->assertContains('kcfinder/browse.php?lang=it&type=docs', $this->viewVariable('kcfinder'));

        //GET request. Now only the `images` type exists
        safe_rmdir(UPLOADED . 'docs');
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/browser.ctp');
        $this->assertEquals(['images' => 'images'], $this->viewVariable('types'));
        $this->assertContains('kcfinder/browse.php?lang=it&type=images', $this->viewVariable('kcfinder'));
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
        $this->assertTemplate('Admin/Systems/changelogs.ctp');
        $this->assertNotEmpty($this->viewVariable('files'));
        $this->assertIsArray($this->viewVariable('files'));
        $this->assertEmpty($this->viewVariable('changelog'));

        //GET request. Asks for a changelog file
        $this->get($url + ['?' => ['file' => ME_CMS]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/changelogs.ctp');
        $this->assertIsString($this->viewVariable('changelog'));
    }

    /**
     * Tests for `checkup()` method
     * @test
     */
    public function testCheckup()
    {
        $this->get($this->url + ['action' => 'checkup']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/checkup.ctp');

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
        foreach ($expectedViewVars as $varName) {
            $this->assertNotEmpty($this->viewVariable($varName));
        }
    }

    /**
     * Tests for `clearSitemap()` method
     * @test
     */
    public function testClearSitemap()
    {
        safe_unlink(SITEMAP);
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
        $this->assertFileNotExists($files);

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
        $this->createSomeTemporaryData();
        $this->get($this->url + ['action' => 'tmpViewer']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin/Systems/tmp_viewer.ctp');

        $expectedViewVars = [
            'assetsSize',
            'cacheSize',
            'logsSize',
            'sitemapSize',
            'thumbsSize',
            'totalSize',
            'cacheStatus',
        ];
        foreach ($expectedViewVars as $varName) {
            $this->assertNotEmpty($this->viewVariable($varName));
        }
    }
}
