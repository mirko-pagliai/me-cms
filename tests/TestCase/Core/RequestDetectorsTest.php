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
namespace MeCms\Test\TestCase\Core;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\TestSuite\TestCase;

/**
 * RequestDetectorsTest class
 */
class RequestDetectorsTest extends TestCase
{
    /**
     * @var \Cake\Network\Request
     */
    public $Request;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        //Creates request
        $this->Request = new Request;
        $this->Request = $this->Request->withParam('action', 'add')
            ->withParam('controller', 'myController')
            ->withParam('prefix', 'myPrefix');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Request);
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
        $this->assertFalse($this->Request->is('delete'));
        $this->assertFalse($this->Request->is('edit'));
        $this->assertFalse($this->Request->is('index'));
        $this->assertFalse($this->Request->is('view'));

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

        //Creates request
        $this->Request = new Request;
        $this->Request = $this->Request->withParam('prefix', ADMIN_PREFIX);

        $this->assertTrue($this->Request->isAdmin());
        $this->assertTrue($this->Request->is('admin'));
    }

    /**
     * Tests for `is('banned')` detector
     * @test
     */
    public function testIsBanned()
    {
        $this->assertFalse($this->Request->isBanned());
        $this->assertNull($this->Request->session()->read('allowed_ip'));

        //It is NOT banned. This is not the same IP
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.98']);
        $this->assertFalse($this->Request->isBanned());
        $this->assertTrue($this->Request->session()->read('allowed_ip'));

        //It is NOT banned. None of the IP coincided
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.97', '99.99.99.98']);
        $this->assertFalse($this->Request->isBanned());
        $this->assertTrue($this->Request->session()->read('allowed_ip'));

        //It is NOT banned. None of the IP coincided
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.98.*', '99.99.*.98']);
        $this->assertFalse($this->Request->isBanned());
        $this->assertTrue($this->Request->session()->read('allowed_ip'));

        //It is banned. This is the same IP
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->session()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.98', '99.99.99.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->session()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.*']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->session()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.*.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->session()->read('allowed_ip'));
        $this->assertNull($this->Request->session()->read('allowed_ip'));

        //It is banned. One of the IP coincided
        $this->Request->session()->delete('allowed_ip');
        $this->Request->env('REMOTE_ADDR', '99.99.99.99');
        Configure::write('Banned', ['99.99.99.98', '99.99.*.99']);
        $this->assertTrue($this->Request->isBanned());
        $this->assertNull($this->Request->session()->read('allowed_ip'));
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

        //Creates request
        $this->Request = new Request;

        $this->assertTrue($this->Request->isOffline());
        $this->assertTrue($this->Request->is('offline'));

        //Creates request
        $this->Request = new Request;
        $this->Request = $this->Request->withParam('prefix', ADMIN_PREFIX);

        $this->assertFalse($this->Request->isOffline());
        $this->assertFalse($this->Request->is('offline'));

        //Creates request
        $this->Request = new Request;
        $this->Request = $this->Request->withParam('action', 'offline');

        $this->assertFalse($this->Request->isOffline());
        $this->assertFalse($this->Request->is('offline'));
    }
}
