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
use MeCms\Controller\Admin\SystemsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PhotosControllerTest class
 */
class SystemsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\Admin\SystemsController
     */
    protected $Controller;

    /**
     * @var array
     */
    protected $url;

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
            'assets' => getConfigOrFail(ASSETS . '.target') . DS . 'asset_file',
            'logs' => LOGS . 'log_file',
            'sitemap' => SITEMAP,
            'thumbs' => getConfigOrFail(THUMBER . '.target') . DS . md5('a') . '_' . md5('a') . '.jpg',
        ];

        foreach ($files as $file) {
            file_put_contents($file, str_repeat('a', 255));
        }

        return $files;
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        I18n::setLocale('en_US');

        $this->setUserGroup('admin');

        $this->Controller = new SystemsController;

        Cache::clearAll();

        $this->url = ['controller' => 'Systems', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes all temporary files
        safe_unlink_recursive(getConfigOrFail(ASSETS . '.target'));
        safe_unlink_recursive(getConfigOrFail(THUMBER . '.target'));
        safe_unlink(SITEMAP);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $this->Controller->request = $this->Controller->request->withParam('action', 'browser');
        $this->Controller->initialize();

        $this->assertContains('KcFinder', $this->Controller->components()->loaded());
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

        //`tmpCleaner` action
        $this->Controller = new SystemsController;
        $this->Controller->request = $this->Controller->request->withParam('action', 'tmpCleaner');

        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

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
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEmpty($kcfinderFromView);

        //GET request. Asks for `docs` type
        $this->get($url + ['?' => ['type' => 'docs']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEquals('http://localhost/vendor/kcfinder/browse.php?lang=en&type=docs', $kcfinderFromView);

        I18n::setLocale('it');

        //GET request. Now with `it` locale
        $this->get($url + ['?' => ['type' => 'docs']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEquals('http://localhost/vendor/kcfinder/browse.php?lang=it&type=docs', $kcfinderFromView);

        safe_rmdir(UPLOADED . 'docs');

        //GET request. Now only the `images` type exists
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEquals('http://localhost/vendor/kcfinder/browse.php?lang=it&type=images', $kcfinderFromView);
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
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/changelogs.ctp');

        $filesFromView = $this->viewVariable('files');
        $this->assertNotEmpty($filesFromView);
        $this->assertIsArray($filesFromView);

        $changelogFromView = $this->viewVariable('changelog');
        $this->assertEmpty($changelogFromView);

        //GET request. Asks for a changelog file
        $this->get($url + ['?' => ['file' => ME_CMS]]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/changelogs.ctp');

        $changelogFromView = $this->viewVariable('changelog');
        $this->assertIsString($changelogFromView);
    }

    /**
     * Tests for `checkup()` method
     * @test
     */
    public function testCheckup()
    {
        $this->get($this->url + ['action' => 'checkup']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/checkup.ctp');

        foreach ($this->_controller->viewVars as $var) {
            $this->assertNotEmpty($var);
        }

        $this->assertArrayKeysEqual([
            'webroot',
            'temporary',
            'plugins',
            'phpExtensions',
            'kcfinder',
            'cakephp',
            'cache',
            'backups',
            'apache',
        ], $this->_controller->viewVars);
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
    }

    /**
     * Tests for `tmpCleaner()` method
     * @test
     */
    public function testTmpCleaner()
    {
        $url = $this->url + ['action' => 'tmpCleaner'];

        $files = $this->createSomeTemporaryData();

        //POST request. Cleans all
        $this->post($url + ['all']);
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertCacheIsEmpty();
        $this->assertFileNotExists($files);

        $files = $this->createSomeTemporaryData();

        //POST request. Cleans the cache
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
        $this->assertFlashMessage('The operation has not been performed correctly');

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
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/tmp_viewer.ctp');

        $viewVars = $this->_controller->viewVars;
        ksort($viewVars);

        foreach ($viewVars as $var) {
            $this->assertNotEmpty($var);
        }

        $this->assertArrayKeysEqual([
            'assetsSize',
            'cacheSize',
            'cacheStatus',
            'logsSize',
            'sitemapSize',
            'thumbsSize',
            'totalSize',
        ], $viewVars);
    }
}
