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
namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\SystemsController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;
use Reflection\ReflectionTrait;

/**
 * PhotosControllerTest class
 */
class SystemsControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;
    use ReflectionTrait;

    /**
     * @var \MeCms\Controller\Admin\SystemsController
     */
    protected $Controller;

    /**
     * @var array
     */
    protected $url;

    /**
     * Internal method to create some temporary data (cache, assets, logs,
     *  sitemap, thumbnails)
     */
    protected function createSomeTemporaryData()
    {
        //Writes some cache data
        Cache::write('value', 'data');
        Cache::write('valueFromGroup', 'data', 'posts');

        //Creates some asset files
        file_put_contents(Configure::read('Assets.target') . DS . 'asset_file', str_repeat('a', 10));

        //Creates some log file
        file_put_contents(LOGS . 'log_file', str_repeat('a', 10));

        //Creates a sitemap file
        file_put_contents(SITEMAP, str_repeat('a', 10));

        //Creates a thumbnail
        file_put_contents(Configure::read('Thumbs.target') . DS . 'thumb.jpg', str_repeat('a', 10));
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

        I18n::locale('en_US');

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

        unset($this->Controller);
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
        //@codingStandardsIgnoreLine
        @mkdir(UPLOADED . 'docs');

        $url = array_merge($this->url, ['action' => 'browser']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEmpty($kcfinderFromView);

        //GET request. Asks for `docs` type
        $this->get(array_merge($url, ['?' => ['type' => 'docs']]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEquals('http://localhost/vendor/kcfinder/browse.php?lang=en&type=docs', $kcfinderFromView);

        I18n::locale('it');

        //GET request. Now with `it` locale
        $this->get(array_merge($url, ['?' => ['type' => 'docs']]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/browser.ctp');

        $typesFromView = $this->viewVariable('types');
        $this->assertEquals(['docs' => 'docs', 'images' => 'images'], $typesFromView);

        $kcfinderFromView = $this->viewVariable('kcfinder');
        $this->assertEquals('http://localhost/vendor/kcfinder/browse.php?lang=it&type=docs', $kcfinderFromView);

        //@codingStandardsIgnoreLine
        @rmdir(UPLOADED . 'docs');

        //GET request. Now only the `images` type exists
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
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
        $url = array_merge($this->url, ['action' => 'changelogs']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/changelogs.ctp');

        $filesFromView = $this->viewVariable('files');
        $this->assertTrue(is_array($filesFromView));
        $this->assertNotEmpty($filesFromView);

        $changelogFromView = $this->viewVariable('changelog');
        $this->assertEmpty($changelogFromView);

        //GET request. Asks for a changelog file
        $this->get(array_merge($url, ['?' => ['file' => ME_CMS]]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/changelogs.ctp');

        $changelogFromView = $this->viewVariable('changelog');
        $this->assertTrue(is_string($changelogFromView));
    }

    /**
     * Tests for `checkup()` method
     * @test
     */
    public function testCheckup()
    {
        $this->get(array_merge($this->url, ['action' => 'checkup']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/checkup.ctp');

        $varsFromView = $this->_controller->viewVars;

        foreach ($varsFromView as $var) {
            $this->assertNotEmpty($var);
        }

        $this->assertEquals([
            'webroot',
            'temporary',
            'plugins',
            'phpExtensions',
            'cache',
            'backups',
            'apache',
        ], array_keys($varsFromView));
    }

    /**
     * Tests for `clearSitemap()` method
     * @test
     */
    public function testClearSitemap()
    {
        if (file_exists(SITEMAP)) {
            //@codingStandardsIgnoreLine
            @unlink(SITEMAP);
        }

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
        $url = array_merge($this->url, ['action' => 'tmpCleaner']);

        $this->createSomeTemporaryData();

        //POST request. Cleans all
        $this->post(array_merge($url, ['all']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $this->assertFalse(Cache::read('value'));
        $this->assertFalse(Cache::read('valueFromGroup', 'posts'));
        $this->assertFalse(file_exists(Configure::read('Assets.target') . DS . 'asset_file'));
        $this->assertFalse(file_exists(LOGS . 'log_file'));
        $this->assertFalse(file_exists(SITEMAP));
        $this->assertFalse(file_exists(Configure::read('Thumbs.target') . DS . 'thumb.jpg'));

        $this->createSomeTemporaryData();

        //POST request. Cleans the cache
        $this->post(array_merge($url, ['cache']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $this->assertFalse(Cache::read('value'));
        $this->assertFalse(Cache::read('valueFromGroup', 'posts'));

        //POST request. Cleans assets
        $this->post(array_merge($url, ['assets']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $this->assertFalse(file_exists(Configure::read('Assets.target') . DS . 'asset_file'));

        //POST request. Cleans logs
        $this->post(array_merge($url, ['logs']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $this->assertFalse(file_exists(LOGS . 'log_file'));

        //POST request. Cleans the sitemap
        $this->post(array_merge($url, ['sitemap']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $this->assertFalse(file_exists(SITEMAP));

        //POST request. Cleans thumbnails
        $this->post(array_merge($url, ['thumbs']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        $this->assertFalse(file_exists(Configure::read('Thumbs.target') . DS . 'thumb.jpg'));

        //POST request. Invalid type
        $this->post(array_merge($url, ['invalidType']));
        $this->assertRedirect(['action' => 'tmpViewer']);
        $this->assertSession('The operation has not been performed correctly', 'Flash.flash.0.message');

        //GET request
        $this->get(array_merge($url, ['all']));
        $this->assertResponseError();
    }

    /**
     * Tests for `tmpViewer()` method
     * @test
     */
    public function testTmpViewer()
    {
        $this->createSomeTemporaryData();

        $this->get(array_merge($this->url, ['action' => 'tmpViewer']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Systems/tmp_viewer.ctp');

        $varsFromView = $this->_controller->viewVars;

        foreach ($varsFromView as $var) {
            $this->assertNotEmpty($var);
        }

        $this->assertEquals([
            'assetsSize',
            'cacheSize',
            'logsSize',
            'sitemapSize',
            'thumbsSize',
            'cacheStatus',
            'totalSize',
        ], array_keys($varsFromView));
    }
}
