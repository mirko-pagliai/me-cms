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
use Cake\ORM\Entity;
use MeCms\TestSuite\ControllerTestCase;

/**
 * LogsControllerTest class
 */
class LogsControllerTest extends ControllerTestCase
{
    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Internal method to write some logs
     */
    protected function writeSomeLogs()
    {
        Log::write('error', 'This is an error message');
        Log::write('critical', 'This is a critical message');
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
        $this->assertTemplate('Admin/Logs/index.ctp');

        foreach ($this->viewVariable('logs') as $log) {
            $this->assertInstanceOf(Entity::class, $log);
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
        $this->assertTemplate('Admin/Logs/view.ctp');
        $this->assertContains('This is an error message', $this->viewVariable('content'));
        $this->assertContains('This is a critical message', $this->viewVariable('content'));
        $this->assertEquals('error.log', $this->viewVariable('filename'));
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
        $this->assertTemplate('Admin/Logs/view_as_serialized.ctp');
        $this->assertEquals([
            'This is a critical message',
            'This is an error message',
        ], collection($this->viewVariable('content'))->extract('message')->toArray());
        $this->assertEquals('error.log', $this->viewVariable('filename'));
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
        //POST request
        $this->writeSomeLogs();
        $this->post($this->url + ['action' => 'delete', 'error.log']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertFileNotExists(LOGS . 'error.log');

        //POST request. The log file doesn't exist
        $this->post($this->url + ['action' => 'delete', 'noExisting.log']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);
    }
}
