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

use Cake\Log\Log;
use MeCms\Controller\Admin\LogsController;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * LogsControllerTest class
 */
class LogsControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Controller\Admin\LogsController
     */
    protected $Controller;

    /**
     * @var array
     */
    protected $url;

    /**
     * Internal method to write some logs
     */
    protected function writeSomeLogs()
    {
        Log::write('error', 'This is an error message');
        Log::write('critical', 'This is a critical message');
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

        $this->Controller = new LogsController;

        $this->url = ['controller' => 'Logs', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Tests for `getPath()` method
     * @test
     */
    public function testGetPath()
    {
        $result = $this->invokeMethod($this->Controller, 'getPath', ['file.log', false]);
        $this->assertEquals(LOGS . 'file.log', $result);

        $result = $this->invokeMethod($this->Controller, 'getPath', ['file.log', true]);
        $this->assertEquals(LOGS . 'file_serialized.log', $result);
    }

    /**
     * Tests for `read()` method
     * @test
     */
    public function testRead()
    {
        $this->writeSomeLogs();

        $this->assertNotEmpty($this->invokeMethod($this->Controller, 'read', ['error.log', false]));
        $this->assertNotEmpty($this->invokeMethod($this->Controller, 'read', ['error.log', true]));
    }

    /**
     * Tests for `read()` method, with a not readable file
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory /tmp/me_cms/cakephp_log/noExisting.log not readable
     * @test
     */
    public function testReadNotReadableFile()
    {
        $this->invokeMethod($this->Controller, 'read', ['noExisting.log', false]);
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
        $this->writeSomeLogs();

        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Logs/index.ctp');

        $logsFromView = $this->viewVariable('logs');
        $this->assertCount(1, $logsFromView);

        foreach ($logsFromView as $log) {
            $this->assertInstanceOf('Cake\ORM\Entity', $log);
            $this->assertEquals($log->filename, 'error.log');
            $this->assertTrue($log->hasSerialized);
            $this->assertEquals($log->size, filesize(LOGS . 'error.log'));
        }
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $this->writeSomeLogs();

        $this->get($this->url + ['action' => 'view', 'error.log']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Logs/view.ctp');

        $contentFromView = $this->viewVariable('content');
        $this->assertContains('This is an error message', $contentFromView);
        $this->assertContains('This is a critical message', $contentFromView);

        $filenameFromView = $this->viewVariable('filename');
        $this->assertEquals('error.log', $filenameFromView);
    }

    /**
     * Tests for `view()` method, render as serialized
     * @test
     */
    public function testViewAsSerialized()
    {
        $this->writeSomeLogs();

        $this->get($this->url + ['action' => 'view', 'error.log', '?' => ['as' => 'serialized']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Logs/view_as_serialized.ctp');

        $messagesFromView = collection($this->viewVariable('content'))->extract('message')->toArray();
        $this->assertEquals([
            'This is a critical message',
            'This is an error message',
        ], $messagesFromView);

        $filenameFromView = $this->viewVariable('filename');
        $this->assertEquals('error.log', $filenameFromView);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        $this->writeSomeLogs();

        $this->get($this->url + ['action' => 'download', 'error.log']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertFileResponse(LOGS . 'error.log');
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->writeSomeLogs();

        //POST request
        $this->post($this->url + ['action' => 'delete', 'error.log']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has been performed correctly');

        //POST request. The log file doesn't exist
        $this->post($this->url + ['action' => 'delete', 'noExisting.log']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The operation has not been performed correctly');
    }
}
