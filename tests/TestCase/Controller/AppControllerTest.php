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
namespace MeCms\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use MeCms\Controller\AppController;

/**
 * AppControllerTest class
 */
class AppControllerTest extends TestCase
{
    /**
     * @var \MeCms\Controller\AppController
     */
    public $AppController;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        //Sets some configuration values
        Configure::write(ME_CMS . '.admin.records', 7);
        Configure::write(ME_CMS . '.default.records', 5);
        Configure::write(ME_CMS . '.security.recaptcha', true);
        Configure::write(ME_CMS . '.security.search_interval', 15);

        $this->AppController = $this->getMockBuilder(AppController::class)
            ->setMethods(['isBanned', 'isOffline', 'redirect'])
            ->getMock();

        $this->AppController->method('redirect')->will($this->returnArgument(0));
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->AppController);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $components = collection($this->AppController->components()->loaded())
            ->map(function ($value) {
                return get_class($this->AppController->components()->{$value});
            })
            ->toList();

        $this->assertEquals([
            'Cake\Controller\Component\CookieComponent',
            'MeCms\Controller\Component\AuthComponent',
            'MeTools\Controller\Component\FlashComponent',
            'Cake\Controller\Component\RequestHandlerComponent',
            'MeTools\Controller\Component\UploaderComponent',
            'MeTools\Controller\Component\RecaptchaComponent',
        ], $components);
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->AppController->request->action = 'my-action';
        $this->AppController->request->query['sort'] = 'my-field';

        $this->AppController->beforeFilter(new Event('event'));

        $this->assertEquals(['my-action'], $this->AppController->Auth->allowedActions);
        $this->assertFalse(array_search('sortWhitelist', array_keys($this->AppController->paginate)));
        $this->assertEquals(5, $this->AppController->paginate['limit']);
        $this->assertEquals(5, $this->AppController->paginate['maxLimit']);

        //Admin request
        $this->AppController = new AppController();
        $this->AppController->request->action = 'my-action';
        $this->AppController->request->query['sort'] = 'my-field';
        $this->AppController->request->params['prefix'] = ADMIN_PREFIX;
        $this->AppController->beforeFilter(new Event('event'));

        $this->assertEquals([], $this->AppController->Auth->allowedActions);
        $this->assertEquals(['my-field'], $this->AppController->paginate['sortWhitelist']);
        $this->assertEquals(7, $this->AppController->paginate['limit']);
        $this->assertEquals(7, $this->AppController->paginate['maxLimit']);
    }

    /**
     * Tests for `beforeFilter()` method, with a banned user
     * @test
     */
    public function testBeforeFilterWithBannedUser()
    {
        $this->AppController->method('isBanned')->willReturn(true);

        $beforeFilter = $this->AppController->beforeFilter(new Event('event'));
        $this->assertEquals(['_name' => 'ipNotAllowed'], $beforeFilter);
    }

    /**
     * Tests for `beforeFilter()` method, on offline site
     * @test
     */
    public function testBeforeFilterWithOfflineSite()
    {
        $this->AppController->method('isOffline')->willReturn(true);

        $beforeFilter = $this->AppController->beforeFilter(new Event('event'));
        $this->assertEquals(['_name' => 'offline'], $beforeFilter);
    }

    /**
     * Tests for `beforeRender()` method
     * @test
     */
    public function testBeforeRender()
    {
        $this->AppController->beforeRender(new Event('event'));

        $this->assertEquals(null, $this->AppController->viewBuilder()->layout());
        $this->assertEquals('MeCms.View/App', $this->AppController->viewBuilder()->className());
        $this->assertEquals(['MeCms.Auth' => null], $this->AppController->viewBuilder()->helpers());

        //Admin request
        $this->AppController = new AppController();
        $this->AppController->request->params['prefix'] = ADMIN_PREFIX;
        $this->AppController->beforeRender(new Event('event'));

        $this->assertEquals('MeCms.View/Admin', $this->AppController->viewBuilder()->className());

        //Ajax request
        $this->AppController->request->env('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');

        $this->AppController->beforeRender(new Event('event'));
        $this->assertEquals('MeCms.ajax', $this->AppController->viewBuilder()->layout());
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        $this->assertFalse($this->AppController->isAuthorized());

        $this->AppController->components()->Auth->setUser(['group' => ['name' => 'admin']]);
        $this->assertTrue($this->AppController->isAuthorized());

        $this->AppController->components()->Auth->setUser(['group' => ['name' => 'manager']]);
        $this->assertTrue($this->AppController->isAuthorized());

        $this->AppController->components()->Auth->setUser(['group' => ['name' => 'user']]);
        $this->assertFalse($this->AppController->isAuthorized());
    }
}
