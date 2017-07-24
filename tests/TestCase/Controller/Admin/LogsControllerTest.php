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
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Controller\Admin\LogsController;
use MeCms\TestSuite\Traits\AuthMethodsTrait;
use Reflection\ReflectionTrait;

/**
 * LogsControllerTest class
 */
class LogsControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;
    use ReflectionTrait;

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
    protected function _writeSomeLogs()
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

        Log::drop('error');
        Log::setConfig('error', [
            'className' => 'MeCms\Log\Engine\SerializedLog',
            'path' => LOGS,
            'file' => 'error',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
            'url' => env('LOG_ERROR_URL', null),
        ]);

        $this->setUserGroup('admin');

        $this->Controller = new LogsController;

        $this->url = ['controller' => 'Logs', 'prefix' => ADMIN_PREFIX, 'plugin' => ME_CMS];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        //Deletes all backups
        foreach (glob(LOGS . '*') as $file) {
            //@codingStandardsIgnoreLine
            @unlink($file);
        }

        unset($this->Controller);
    }

    /**
     * Tests for `_path()` method
     * @test
     */
    public function testPath()
    {
        $result = $this->invokeMethod($this->Controller, '_path', ['file.log']);
        $this->assertEquals(LOGS . 'file.log', $result);

        $result = $this->invokeMethod($this->Controller, '_path', ['file.log', true]);
        $this->assertEquals(LOGS . 'file_serialized.log', $result);
    }

    /**
     * Tests for `_read()` method
     * @test
     */
    public function testRead()
    {
        $this->_writeSomeLogs();

        $this->assertNotEmpty($this->invokeMethod($this->Controller, '_read', ['error.log']));

        $this->assertNotEmpty($this->invokeMethod($this->Controller, '_read', ['error.log', true]));
    }

    /**
     * Tests for `_read()` method, with a not readable file
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage File or directory /tmp/me_cms/cakephp_log/noExisting.log not readable
     * @test
     */
    public function testReadNotReadableFile()
    {
        $this->invokeMethod($this->Controller, '_read', ['noExisting.log']);
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
        $this->_writeSomeLogs();

        $this->get(array_merge($this->url, ['action' => 'index']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Logs/index.ctp');

        $logsFromView = $this->viewVariable('logs');
        $this->assertTrue(is_array($logsFromView));
        $this->assertNotEmpty($logsFromView);

        $logs = collection($logsFromView)->map(function ($log) {
            return (array)$log;
        })->toList();

        $this->assertEquals([
            [
                'filename' => 'error.log',
                'hasSerialized' => true,
                'size' => filesize(LOGS . 'error.log'),
            ],
        ], $logs);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $this->_writeSomeLogs();

        $this->get(array_merge($this->url, ['action' => 'view', 'error.log']));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Logs/view.ctp');

        $contentFromView = $this->viewVariable('content');
        $this->assertNotEmpty('some data', $contentFromView);

        $filenameFromView = $this->viewVariable('filename');
        $this->assertEquals('error.log', $filenameFromView);
    }

    /**
     * Tests for `view()` method, render as serialized
     * @test
     */
    public function testViewAsSerialized()
    {
        $this->_writeSomeLogs();

        $this->get(array_merge($this->url, ['action' => 'view', 'error.log', '?' => ['as' => 'serialized']]));
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Admin/Logs/view_as_serialized.ctp');

        $contentFromView = $this->viewVariable('content');
        $this->assertNotEmpty('some data', $contentFromView);

        $filenameFromView = $this->viewVariable('filename');
        $this->assertEquals('error.log', $filenameFromView);
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload()
    {
        $this->_writeSomeLogs();

        $this->get(array_merge($this->url, ['action' => 'download', 'error.log']));
        $this->assertResponseOk();
        $this->assertFileResponse(LOGS . 'error.log');
    }

    /**
     * Tests for `delete()` method
     * @test
     */
    public function testDelete()
    {
        $this->_writeSomeLogs();

        //POST request
        $this->post(array_merge($this->url, ['action' => 'delete', 'error.log']));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has been performed correctly', 'Flash.flash.0.message');

        //POST request. The log file doesn't exist
        $this->post(array_merge($this->url, ['action' => 'delete', 'noExisting.log']));
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The operation has not been performed correctly', 'Flash.flash.0.message');
    }
}
