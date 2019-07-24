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
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
    {
        //Sets some configuration values
        Configure::write('MeCms.admin.records', 7);
        Configure::write('MeCms.default.records', 5);
        Configure::write('MeCms.security.recaptcha', true);
        Configure::write('MeCms.security.search_interval', 15);

        $controller = $this->getMockForController();
        $controller->request = $this->Controller->getRequest()->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field']);
        $controller->beforeFilter(new Event('myEvent'));
        $this->assertNotEmpty($controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 5, 'maxLimit' => 5], $controller->paginate);
        $this->assertNull($controller->viewBuilder()->getLayout());
        $this->assertEquals('MeCms.View/App', $controller->viewBuilder()->getClassName());

        //Admin request
        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withParam('action', 'my-action')
            ->withQueryParams(['sort' => 'my-field'])
            ->withParam('prefix', ADMIN_PREFIX);
        $controller->beforeFilter(new Event('myEvent'));
        $this->assertEmpty($controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $controller->paginate);
        $this->assertEquals('MeCms.View/Admin', $controller->viewBuilder()->getClassName());

        //Ajax request
        $controller = $this->getMockForController();
        $controller->request = $controller->getRequest()->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
        $controller->beforeFilter(new Event('myEvent'));
        $this->assertEquals('MeCms.ajax', $controller->viewBuilder()->getLayout());

        //If the user has been reported as a spammer this makes a redirect
        $controller = $this->getMockForController(null, ['isSpammer']);
        $controller->method('isSpammer')->willReturn(true);
        $this->_response = $controller->beforeFilter(new Event('myEvent'));
        $this->assertRedirect(['_name' => 'ipNotAllowed']);

        //If the site is offline this makes a redirect
        Configure::write('MeCms.default.offline', true);
        $controller = $this->getMockForController();
        $this->_response = $controller->beforeFilter(new Event('myEvent'));
        $this->assertRedirect(['_name' => 'offline']);
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
            $this->Controller->setRequest($this->Controller->getRequest()->withParam('prefix', $prefix));
            $this->Controller->getRequest()->clearDetectorCache();
            $this->assertFalse($this->Controller->isAuthorized());
        }
    }
}
