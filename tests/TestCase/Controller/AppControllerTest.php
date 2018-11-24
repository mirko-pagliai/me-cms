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
     * @var \Cake\Event\Event
     */
    protected $Event;

    /**
     * Mocks a controller
     * @param string $className Controller class name
     * @param array|null $methods The list of methods to mock
     * @param string $alias Controller alias
     * @return object
     * @uses getClassAlias()
     */
    protected function getMockForController($className = null, $methods = null, $alias = 'App')
    {
        $className = $className ?: AppController::class;

        return parent::getMockForController($className, $methods, $alias);
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
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        $expectedComponents = [
            'Auth',
            'Cookie',
            'Flash',
            'Recaptcha',
            'RequestHandler',
            'Uploader',
        ];
        foreach ($expectedComponents as $component) {
            $this->assertHasComponent($component);
        }
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        $controller = $this->getMockForController();
        $controller->request = $controller->request
            ->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field']);
        $controller->beforeFilter($this->Event);
        $this->assertNotEmpty($controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 5, 'maxLimit' => 5], $controller->paginate);
        $this->assertNull($controller->viewBuilder()->getLayout());
        $this->assertEquals('MeCms.View/App', $controller->viewBuilder()->getClassName());

        //Admin request
        $controller = $this->getMockForController();
        $controller->request = $controller->request
            ->withParam('action', 'my-action')
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

        //Request with banned user
        $controller = $this->getMockForController(null, ['isBanned']);
        $controller->method('isBanned')->willReturn(true);
        $this->_response = $controller->beforeFilter($this->Event);
        $this->assertRedirect(['_name' => 'ipNotAllowed']);

        //Request with offline site
        $controller = $this->getMockForController(null, ['isOffline']);
        $controller->method('isOffline')->willReturn(true);
        $this->_response = $controller->beforeFilter($this->Event);
        $this->assertRedirect(['_name' => 'offline']);
    }

    /**
     * Tests for `beforeRender()` method
     * @test
     */
    public function testBeforeRender()
    {
        $expectedHelpers = ['Recaptcha.Recaptcha', 'MeCms.Auth'];
        $this->Controller->beforeRender($this->Event);
        $this->assertArrayKeysEqual($expectedHelpers, $this->Controller->viewBuilder()->getHelpers());
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
    {
        parent::testIsAuthorized();

        //With `admin` prefix
        $this->Controller->request = $this->Controller->request->withParam('prefix', ADMIN_PREFIX);
        $this->Controller->request->clearDetectorCache();
        parent::testIsAuthorized();

        //With other prefix
        $this->Controller->request = $this->Controller->request->withParam('prefix', 'otherPrefix');
        $this->Controller->request->clearDetectorCache();
        parent::testIsAuthorized();
    }
}
