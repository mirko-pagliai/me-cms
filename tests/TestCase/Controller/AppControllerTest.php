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
namespace MeCms\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use MeCms\Controller\AppController;
use MeCms\TestSuite\ControllerTestCase;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * @var \Cake\Event\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Event;

    /**
     * Mocks a controller
     * @param string $className Controller class name
     * @param array|null $methods The list of methods to mock
     * @param string $alias Controller alias
     * @return \Cake\Controller\Controller|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockForController($className = null, $methods = null, $alias = 'App')
    {
        return parent::getMockForController($className ?: AppController::class, $methods, $alias);
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        //Sets some configuration values
        Configure::write('MeCms.admin.records', 7);
        Configure::write('MeCms.default.records', 5);
        Configure::write('MeCms.security.recaptcha', true);
        Configure::write('MeCms.security.search_interval', 15);

        $this->Controller = $this->getMockForController();
        $this->Event = $this->getMockBuilder(Event::class)
            ->setConstructorArgs(['exampleEvent'])
            ->getMock();
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $this->Controller->request = $this->Controller->request->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field']);
        $this->Controller->beforeFilter($this->Event);
        $this->assertNotEmpty($this->Controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 5, 'maxLimit' => 5], $this->Controller->paginate);
        $this->assertNull($this->Controller->viewBuilder()->getLayout());
        $this->assertEquals('MeCms.View/App', $this->Controller->viewBuilder()->getClassName());

        //Admin request
        $controller = $this->getMockForController();
        $controller->request = $controller->request->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field'])
            ->withParam('prefix', ADMIN_PREFIX);
        $controller->beforeFilter($this->Event);
        $this->assertEmpty($controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $controller->paginate);
        $this->assertEquals('MeCms.View/Admin', $controller->viewBuilder()->getClassName());

        //Ajax request
        $controller = $this->getMockForController();
        $controller->request = $controller->request->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
        $controller->beforeFilter($this->Event);
        $this->assertEquals('MeCms.ajax', $controller->viewBuilder()->getLayout());

        //Methods that, if they return `true`, make a redirect
        foreach ([
            'isSpammer' => 'ipNotAllowed',
            'isOffline' => 'offline',
        ] as $method => $expectedRedirect) {
            $controller = $this->getMockForController(null, [$method]);
            $controller->method($method)->willReturn(true);
            $this->_response = $controller->beforeFilter($this->Event);
            $this->assertRedirect(['_name' => $expectedRedirect]);
        }
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        parent::testIsAuthorized();

        //With prefixes
        foreach ([ADMIN_PREFIX, 'otherPrefix'] as $prefix) {
            $this->Controller->request = $this->Controller->request->withParam('prefix', $prefix);
            $this->Controller->request->clearDetectorCache();
            parent::testIsAuthorized();
        }
    }
}
