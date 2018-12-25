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
namespace MeCms\Test\TestCase\Core;

use Cake\Core\Configure;
use Cake\Network\Request;
use MeCms\TestSuite\TestCase;

/**
 * RequestDetectorsTest class
 */
class RequestDetectorsTest extends TestCase
{
    /**
     * @var \Cake\Network\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    public $Request;

    /**
     * Internal method to mock a request
     * @return \Cake\Network\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockForRequest()
    {
        return $this->getMockBuilder(Request::class)
            ->setMethods(null)
            ->getMock();
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Request = $this->getMockForRequest()
            ->withParam('action', 'add')
            ->withParam('controller', 'myController')
            ->withParam('prefix', 'myPrefix');
    }

    /**
     * Tests for `is('add')`, `is('delete')`, `is('edit')`, `is('index')` and
     *  `is('view')` detectors
     * @test
     */
    public function testIsActionName()
    {
        $this->assertTrue($this->Request->isAdd());
        $this->assertFalse($this->Request->isDelete());
        $this->assertFalse($this->Request->isEdit());
        $this->assertFalse($this->Request->isIndex());
        $this->assertFalse($this->Request->isView());

        $this->assertTrue($this->Request->is('add'));

        foreach (['delete', 'edit', 'index', 'view'] as $action) {
            $this->assertFalse($this->Request->is($action));
        }

        $this->assertTrue($this->Request->is(['add', 'edit']));
        $this->assertFalse($this->Request->is(['delete', 'edit']));
    }

    /**
     * Tests for `is('admin')` detector
     * @test
     */
    public function testIsAdmin()
    {
        $this->assertFalse($this->Request->isAdmin());
        $this->assertFalse($this->Request->is('admin'));

        $request = $this->getMockForRequest()->withParam('prefix', ADMIN_PREFIX);
        $this->assertTrue($request->isAdmin());
        $this->assertTrue($request->is('admin'));
    }

    /**
     * Tests for `is('banned')` detector
     * @test
     */
    public function testIsBanned()
    {
        $this->assertFalse($this->Request->isBanned());
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));

        //It is NOT banned. This is not the same IP
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.98']);
        $this->assertFalse($this->Request->isBanned());
        $this->assertTrue($this->Request->getSession()->read('allowed_ip'));

        //It is NOT banned. None of the IP coincided
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.97', '99.99.99.98']);
        $this->assertFalse($this->Request->isBanned());
        $this->assertTrue($this->Request->getSession()->read('allowed_ip'));

        //It is NOT banned. None of the IP coincided
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.98.*', '99.99.*.98']);
        $this->assertFalse($this->Request->isBanned());
        $this->assertTrue($this->Request->getSession()->read('allowed_ip'));

        //It is banned. This is the same IP
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.98', '99.99.99.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.*']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.*.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->getSession()->delete('allowed_ip');
        $this->Request = $this->Request->withEnv('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.98', '99.99.*.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->getSession()->read('allowed_ip'));
    }

    /**
     * Tests for `is('offline')` detector
     * @test
     */
    public function testIsOffline()
    {
        $this->assertFalse($this->Request->isOffline());
        $this->assertFalse($this->Request->is('offline'));

        Configure::write('MeCms.default.offline', true);

        $request = $this->getMockForRequest();
        $this->assertTrue($request->isOffline());
        $this->assertTrue($request->is('offline'));

        $request = $this->getMockForRequest()->withParam('prefix', ADMIN_PREFIX);
        $this->assertTrue($request->isAdmin());
        $this->assertFalse($request->isOffline());
        $this->assertFalse($request->is('offline'));

        $request = $this->getMockForRequest()->withParam('action', 'offline');
        $this->assertFalse($request->isOffline());
        $this->assertFalse($request->is('offline'));
    }
}
