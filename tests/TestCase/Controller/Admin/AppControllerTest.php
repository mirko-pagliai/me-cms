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

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use MeCms\TestSuite\ControllerTestCase;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Controller->setRequest($this->Controller->getRequest()->withParam('prefix', ADMIN_PREFIX));
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        parent::testBeforeFilter();

        Configure::write('MeCms.admin.records', 7);

        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEmpty($this->Controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $this->Controller->paginate);
        $this->assertEquals('MeCms.View/Admin', $this->Controller->viewBuilder()->getClassName());

        //Ajax request
        $this->Controller->setRequest($this->Controller->getRequest()->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest'));
        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEquals('MeCms.ajax', $this->Controller->viewBuilder()->getLayout());

        //If the site is offline this makes a redirect
        //This works anyway, because the admin interface never goes offline
        Configure::write('MeCms.default.offline', true);
        $this->Controller->getRequest()->clearDetectorCache();
        $this->assertNull($this->Controller->beforeFilter(new Event('myEvent')));
    }

    /**
     * Tests for `beforeRender()` method
     * @test
     */
    public function testBeforeRender()
    {
        $this->Controller->beforeRender(new Event('myEvent'));
        $this->assertNull($this->Controller->getRequest()->getSession()->read('referer'));

        $request = $this->Controller->getRequest()->withParam('controller', 'MyController')->withParam('action', 'edit');
        $this->Controller->setRequest($request)->beforeRender(new Event('myEvent'));
        $this->assertNull($this->Controller->getRequest()->getSession()->read('referer'));

        $request = $this->Controller->getRequest()->withParam('action', 'index');
        $this->Controller->setRequest($request)->beforeRender(new Event('myEvent'));
        $result = $this->Controller->getRequest()->getSession()->read('referer');
        $this->assertEquals(['controller' => 'MyController', 'target' => '/'], $result);
    }

    /**
     * Tests for `referer()` method
     * @test
     */
    public function testReferer()
    {
        $request = $this->Controller->getRequest()->withParam('controller', 'MyController')->withParam('action', 'edit');
        $this->assertSame('http://localhost/', $this->Controller->setRequest($request)->referer());

        $session = new Session();
        $session->write('referer', ['controller' => 'MyController', 'target' => '/here']);
        $request = new ServerRequest(compact('session'));
        $request = $request->withParam('controller', 'MyController')->withParam('action', 'edit');
        $this->assertSame('/here', $this->Controller->setRequest($request)->referer(['action' => 'index']));
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
    }
}
