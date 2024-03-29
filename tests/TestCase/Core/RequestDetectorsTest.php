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

namespace MeCms\Test\TestCase\Core;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use MeCms\TestSuite\TestCase;

/**
 * RequestDetectorsTest class
 */
class RequestDetectorsTest extends TestCase
{
    /**
     * @var \Cake\Http\ServerRequest
     */
    public ServerRequest $Request;

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->Request ??= (new ServerRequest())
            ->withParam('action', 'add')
            ->withParam('controller', 'myController')
            ->withParam('prefix', 'myPrefix');
    }

    /**
     * Tests for `is('add')`, `is('delete')`, `is('edit')`, `is('index')` and
     *  `is('view')` detectors
     * @test
     */
    public function testIsActionName(): void
    {
        foreach (['delete', 'edit', 'index', 'view'] as $action) {
            $this->assertFalse($this->Request->is($action));
        }

        $this->assertTrue($this->Request->is('add'));
        $this->assertTrue($this->Request->is(['add', 'edit']));
        $this->assertFalse($this->Request->is(['delete', 'edit']));
    }

    /**
     * Tests for `is('admin')` detector
     * @test
     */
    public function testIsAdmin(): void
    {
        $this->assertFalse($this->Request->is('admin'));
        $this->assertTrue((new ServerRequest())->withParam('prefix', ADMIN_PREFIX)->is('admin'));
    }

    /**
     * Tests for `is('offline')` detector
     * @test
     */
    public function testIsOffline(): void
    {
        $this->assertFalse($this->Request->is('offline'));

        Configure::write('MeCms.default.offline', true);
        $this->assertTrue((new ServerRequest())->is('offline'));

        $request = (new ServerRequest())->withParam('prefix', ADMIN_PREFIX);
        $this->assertTrue($request->is('admin'));
        $this->assertFalse($request->is('offline'));

        $this->assertFalse((new ServerRequest())->withParam('action', 'offline')->is('offline'));
    }
}
