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

use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
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
     * @return void
     */
    protected function writeSomeLogs(): void
    {
        Log::write('error', 'This is an error message');
        Log::write('critical', 'This is a critical message');
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized(): void
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
    public function testIndex(): void
    {
        $this->writeSomeLogs();
        $this->get($this->url + ['action' => 'index']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Logs' . DS . 'index.php');

        $this->assertNotEmpty($this->viewVariable('logs'));
        foreach ($this->viewVariable('logs') as $log) {
            $this->assertInstanceOf(Entity::class, $log);
            $this->assertEquals($log->get('filename'), 'error.log');
            $this->assertTrue($log->get('hasSerialized'));
            $this->assertEquals($log->get('size'), filesize(LOGS . 'error.log'));
        }
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView(): void
    {
        $this->writeSomeLogs();
        $this->get($this->url + ['action' => 'view', 'error.log']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Logs' . DS . 'view.php');
        $this->assertStringContainsString('This is an error message', $this->viewVariable('content'));
        $this->assertStringContainsString('This is a critical message', $this->viewVariable('content'));
        $this->assertEquals('error.log', $this->viewVariable('filename'));
    }

    /**
     * Tests for `view()` method, render as serialized
     * @test
     */
    public function testViewAsSerialized(): void
    {
        $this->writeSomeLogs();
        $this->get($this->url + ['action' => 'view', 'error.log', '?' => ['as' => 'serialized']]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Admin' . DS . 'Logs' . DS . 'view_as_serialized.php');
        $messages = Hash::extract($this->viewVariable('content'), '{*}.message');
        $this->assertEquals(['This is a critical message', 'This is an error message'], $messages);
        $this->assertEquals('error.log', $this->viewVariable('filename'));
    }

    /**
     * Tests for `download()` method
     * @test
     */
    public function testDownload(): void
    {
        $this->writeSomeLogs();
        $this->get($this->url + ['action' => 'download', 'error.log']);
        $this->assertFileResponse(LOGS . 'error.log');
    }

    /**
     * Tests for `delete()` method
     * @requires OS Linux
     * @test
     */
    public function testDelete(): void
    {
        $this->writeSomeLogs();

        //POST request. The log file doesn't exist
        $this->post($this->url + ['action' => 'delete', 'noExisting.log']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_NOT_OK);

        //POST request
        $this->post($this->url + ['action' => 'delete', 'error.log']);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage(I18N_OPERATION_OK);
        $this->assertFileDoesNotExist(LOGS . 'error.log');
    }
}
