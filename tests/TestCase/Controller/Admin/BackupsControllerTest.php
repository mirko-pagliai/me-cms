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
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\BackupsController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * BackupsControllerTest class
 */
class BackupsControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\Admin\BackupsController
     */
    protected $Controller;

    /**
     * @var array
     */
    protected $url;

    /**
     * Internal method to create a backup file
     * @return string File path
     */
    protected function createBackup()
    {
        $file = Configure::read('MysqlBackup.target') . DS . 'backup.sql';
        file_put_contents($file, null);

        return $file;
    }

    /**
     * Internal method to create some backup files
     * @return array Files paths
     */
    protected function createSomeBackups()
    {
        foreach (['sql', 'sql.gz', 'sql.bz2'] as $k => $ext) {
            $files[$k] = Configure::read('MysqlBackup.target') . DS . 'backup.' . $ext;
            file_put_contents($files[$k], null);
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

        $this->setUserGroup('admin');

        $this->Controller = new BackupsController;

        $this->url = ['controller' => 'Backups', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->setUserGroup(null);

        //Deletes all backups
        foreach (glob(Configure::read('MysqlBackup.target') . DS . '*') as $file) {
            //@codingStandardsIgnoreLine
            @unlink($file);
        }

        unset($this->Controller);
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => false,
            'user' => false,
        ]);
    }

    /**
     * Tests for `index()` method
     * @test
     */
    public function testIndex()
    {
        //Creates some backup files
        $this->createSomeBackups();

        $url = array_merge($this->url, ['action' => 'index']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Backups/index.ctp');

        $backupsFromView = $this->viewVariable('backups');
        $this->assertNotEmpty($backupsFromView->toArray());

        foreach ($backupsFromView as $backup) {
            $this->assertInstanceof('stdClass', $backup);
        }
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Backups/add.ctp');

        $backupFromView = $this->viewVariable('backup');
        $this->assertInstanceof('MeCms\Form\BackupForm', $backupFromView);

        //POST request. For now, data are invalid
        $this->post($url, ['filename' => 'backup.txt']);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        //POST request. Now, data are valid
        $this->post($url, ['filename' => 'my_backup.sql']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //The backup file has been created
        $this->assertTrue(file_exists(Configure::read('MysqlBackup.target') . DS . 'my_backup.sql'));
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        //Creates a backup file
        $file = $this->createBackup();

        $url = array_merge($this->url, ['action' => 'delete']);

        $this->post(array_merge($url, [urlencode(basename($file))]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //The backup no longer exists
        $this->assertFalse(file_exists($file));
    }

    /**
     * Tests for `deleteAll()` method
     * @test
     */
    public function testDeleteAll()
    {
        //Creates some backup files
        $files = $this->createSomeBackups();

        $url = array_merge($this->url, ['action' => 'deleteAll']);

        $this->post($url);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //Backups no longer exist
        foreach ($files as $file) {
            $this->assertFalse(file_exists($file));
        }
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        //Creates a backup file
        $file = $this->createBackup();

        $url = array_merge($this->url, ['action' => 'download', urlencode(basename($file))]);

        $this->get($url);
        $this->assertResponseOk();
        $this->assertFileResponse($file);
    }

    /**
     * Tests for `restore()` method
     * @test
     */
    public function testRestore()
    {
        //Creates a backup file
        $file = $this->createBackup();

        //Writes some cache data
        Cache::writeMany(['firstKey' => 'firstValue', 'secondKey' => 'secondValue']);

        $url = array_merge($this->url, ['action' => 'restore', urlencode(basename($file))]);

        $this->post($url);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //Cache data no longer exists
        $this->assertFalse(Cache::read('firstKey'));
        $this->assertFalse(Cache::read('secondKey'));
    }
}
