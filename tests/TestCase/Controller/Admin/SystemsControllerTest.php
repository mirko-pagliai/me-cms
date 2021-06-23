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
use MeCms\Controller\Admin\SystemsController;
use MeCms\TestSuite\ControllerTestCase;
use Tools\Filesystem;

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
        $Filesystem = new Filesystem();

        //Writes some cache data
        Cache::write('value', 'data');
        Cache::write('valueFromGroup', 'data', 'posts');

        $files = [
            'assets' => getConfigOrFail('Assets.target') . DS . 'asset_file',
            'assets2' => getConfigOrFail('Assets.target') . DS . 'asset_file2',
            'logs' => LOGS . 'log_file',
            'sitemap' => SITEMAP,
            'thumbs' => $Filesystem->addSlashTerm(THUMBER_TARGET) . md5('a') . '_' . md5('a') . '.jpg',
        ];

        foreach ($files as $file) {
            $Filesystem->createFile($file, str_repeat('a', 255));
        }

        return $files;
    }

    /**
     * Called after the last test of this test class is run
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        I18n::setLocale('en_US');
        @unlink(SITEMAP);
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown(): void
    {
        Cache::clearAll();

        array_map([new Filesystem(), 'unlinkRecursive'], [getConfigOrFail('Assets.target'), THUMBER_TARGET]);

        parent::tearDown();
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized(): void
    {
        parent::testIsAuthorized();

        //With `tmpCleaner` action
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ], 'tmpCleaner');

        foreach (['all', 'logs'] as $param) {
            $this->Controller->setRequest($this->Controller->getRequest()->withParam('pass.0', $param));
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
    public function testBrowser(): void
    {
        $this->get($this->url + ['action' => 'browser']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Systems' . DS . 'browser.php');
        $this->assertStringEndsWith('elfinder/elfinder.html', $this->viewVariable('explorer'));

        /** @var \MeCms\Controller\Admin\SystemsController&\PHPUnit\Framework\MockObject\MockObject $Controller */
        $Controller = $this->getMockForController(SystemsController::class, ['elFinderExists']);
        $Controller->method('elFinderExists')->willReturn(false);
        $this->_response = $Controller->browser();
        $this->assertRedirect(['_name' => 'dashboard']);
        $this->assertFlashMessage('ElFinder not available');
    }

    /**
     * Tests for `changelogs()` method
     * @test
     */
    public function testChangelogs(): void
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
     * Tests for `tmpCleaner()` method
     * @test
     */
    public function testTmpCleaner(): void
    {
        $url = $this->url + ['action' => 'tmpCleaner'];

        //POST request. Cleans all
        $files = $this->createSomeTemporaryData();
        $this->post($url + ['all']);
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertCacheIsEmpty();
        array_map([$this, 'assertFileDoesNotExist'], $files);

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
            $this->assertFileDoesNotExist($files[$tmpName]);
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
    public function testTmpViewer(): void
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
