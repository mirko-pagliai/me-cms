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
use Cake\Log\Log;
use MeCms\Controller\Admin\BackupsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * BackupsControllerTest class
 */
class BackupsControllerTest extends IntegrationTestCase
{
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
        $file = getConfigOrFail(DATABASE_BACKUP . '.target') . DS . 'backup.sql';
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
            $files[$k] = getConfigOrFail(DATABASE_BACKUP . '.target') . DS . 'backup.' . $ext;
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

        //Deletes all backups
        foreach (glob(getConfigOrFail(DATABASE_BACKUP . '.target') . DS . '*') as $file) {
            //@codingStandardsIgnoreLine
            @unlink($file);
        }
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        if ($this->getName() === 'testSend') {
            //Only for the `testSend` test, mocks the `send()` method of
            //  `BackupManager` class, so that it writes on the debug log
            //  instead of sending a real mail
            $controller->BackupManager = $this->getMockBuilder(BackupManager::class)
                ->setMethods(['send'])
                ->getMock();

            $controller->BackupManager->method('send')
                ->will($this->returnCallback(function () {
                    $args = implode(', ', array_map(function ($arg) {
                        return '`' . $arg . '`';
                    }, func_get_args()));

                    return Log::write('debug', 'Called `send()` with args: ' . $args);
                }));
        }

        $controller->viewBuilder()->setLayout('with_flash');

        parent::controllerSpy($event, $controller);
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

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Backups/index.ctp');

        $backupsFromView = $this->viewVariable('backups');
        $this->assertNotEmpty($backupsFromView->toArray());
        $this->assertInstanceof('Cake\ORM\Entity', $backupsFromView);
    }

    /**
     * Tests for `add()` method
     * @test
     */
    public function testAdd()
    {
        $url = array_merge($this->url, ['action' => 'add']);

        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Backups/add.ctp');

        $backupFromView = $this->viewVariable('backup');
        $this->assertNotEmpty($backupFromView);
        $this->assertInstanceof('MeCms\Form\BackupForm', $backupFromView);

        //POST request. Data are invalid
        $this->post($url, ['filename' => 'backup.txt']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertResponseContains('The operation has not been performed correctly');

        //POST request. Now data are valid
        $this->post($url, ['filename' => 'my_backup.sql']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
        $this->assertFileExists(getConfigOrFail(DATABASE_BACKUP . '.target') . DS . 'my_backup.sql');
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        //Creates a backup file
        $file = $this->createBackup();

        $this->post(array_merge(array_merge($this->url, ['action' => 'delete']), [urlencode(basename($file))]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
        $this->assertFileNotExists($file);
    }

    /**
     * Tests for `deleteAll()` method
     * @test
     */
    public function testDeleteAll()
    {
        //Creates some backup files
        $files = $this->createSomeBackups();

        $this->post(array_merge($this->url, ['action' => 'deleteAll']));
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
        $this->assertFileNotExists($files);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        //Creates a backup file
        $file = $this->createBackup();

        $this->get(array_merge($this->url, ['action' => 'download', urlencode(basename($file))]));
        $this->assertResponseOkAndNotEmpty();
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

        $this->post(array_merge($this->url, ['action' => 'restore', urlencode(basename($file))]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');
        $this->assertFalse(Cache::read('firstKey'));
        $this->assertFalse(Cache::read('secondKey'));
    }

    /**
     * Tests for `send()` method
     * @test
     */
    public function testSend()
    {
        //Creates a backup file
        $file = $this->createBackup();

        $this->post(array_merge($this->url, ['action' => 'send', urlencode(basename($file))]));
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        $mail = getConfigOrFail(ME_CMS . '.email.webmaster');
        $this->assertLogContains('Called `send()` with args: `' . $file . '`, `' . $mail . '`', 'debug');
    }
}
