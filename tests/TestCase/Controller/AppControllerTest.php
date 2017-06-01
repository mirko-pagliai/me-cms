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
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * AppControllerTest class
 */
class AppControllerTest extends TestCase
{
    use AuthMethodsTrait;

    /**
     * @var \MeCms\Controller\AppController
     */
    protected $Controller;

    /**
     * @var \Cake\Event\Event
     */
    protected $Event;

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

        $this->Controller = $this->getMockBuilder(AppController::class)
            ->setMethods(['isBanned', 'isOffline', 'redirect'])
            ->getMock();

        $this->Controller->method('redirect')->will($this->returnArgument(0));

        $this->Event = new Event('myEvent');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Controller, $this->Event);
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $componentsInstance = $this->Controller->components();

        $components = collection($componentsInstance->loaded())
            ->map(function ($value) use ($componentsInstance) {
                return get_class($componentsInstance->{$value});
            })->toList();

        $this->assertEquals([
            'Cake\Controller\Component\CookieComponent',
            ME_CMS . '\Controller\Component\AuthComponent',
            METOOLS . '\Controller\Component\FlashComponent',
            'Cake\Controller\Component\RequestHandlerComponent',
            METOOLS . '\Controller\Component\UploaderComponent',
            METOOLS . '\Controller\Component\RecaptchaComponent',
        ], $components);

        $this->assertFalse($this->Controller->Cookie->config('encryption'));
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->assertEmpty($this->Controller->Auth->allowedActions);

        $this->Controller->request = $this->Controller->request
            ->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field']);

        $this->Controller->beforeFilter($this->Event);

        $this->assertNotEmpty($this->Controller->Auth->allowedActions);
        $this->assertFalse(array_search('sortWhitelist', array_keys($this->Controller->paginate)));
        $this->assertEquals(5, $this->Controller->paginate['limit']);
        $this->assertEquals(5, $this->Controller->paginate['maxLimit']);
        $this->assertEquals(null, $this->Controller->viewBuilder()->getLayout());
        $this->assertEquals(ME_CMS . '.View/App', $this->Controller->viewBuilder()->getClassName());

        //Admin request
        $this->Controller = new AppController;
        $this->Controller->request = $this->Controller->request
            ->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field'])
            ->withParam('prefix', ADMIN_PREFIX);

        $this->Controller->beforeFilter($this->Event);
        $this->assertEmpty($this->Controller->Auth->allowedActions);
        $this->assertEquals(['my-field'], $this->Controller->paginate['sortWhitelist']);
        $this->assertEquals(7, $this->Controller->paginate['limit']);
        $this->assertEquals(7, $this->Controller->paginate['maxLimit']);
        $this->assertEquals(ME_CMS . '.View/Admin', $this->Controller->viewBuilder()->getClassName());

        //Ajax request
        $this->Controller->request->env('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->Controller->beforeFilter($this->Event);
        $this->assertEquals(ME_CMS . '.ajax', $this->Controller->viewBuilder()->getLayout());
    }

    /**
     * Tests for `beforeFilter()` method, with a banned user
     * @test
     */
    public function testBeforeFilterWithBannedUser()
    {
        $this->Controller->method('isBanned')->willReturn(true);

        $beforeFilter = $this->Controller->beforeFilter($this->Event);
        $this->assertEquals(['_name' => 'ipNotAllowed'], $beforeFilter);
    }

    /**
     * Tests for `beforeFilter()` method, on offline site
     * @test
     */
    public function testBeforeFilterWithOfflineSite()
    {
        $this->Controller->method('isOffline')->willReturn(true);

        $beforeFilter = $this->Controller->beforeFilter($this->Event);
        $this->assertEquals(['_name' => 'offline'], $beforeFilter);
    }

    /**
     * Tests for `beforeRender()` method
     * @test
     */
    public function testBeforeRender()
    {
        $this->Controller->beforeRender($this->Event);
        $this->assertEquals([ME_CMS . '.Auth' => null], $this->Controller->viewBuilder()->getHelpers());
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        //No prefix
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => true,
        ]);

        //Admin prefix
        $this->Controller = new AppController;
        $this->Controller->request = $this->Controller->request->withParam('prefix', ADMIN_PREFIX);
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);

        //Other prefix
        $this->Controller = new AppController;
        $this->Controller->request = $this->Controller->request->withParam('prefix', 'otherPrefix');
        $this->assertGroupsAreAuthorized([
            'admin' => false,
            'manager' => false,
            'user' => false,
        ]);
    }
}
