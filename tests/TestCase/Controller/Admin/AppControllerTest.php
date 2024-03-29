<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
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

use Authorization\Controller\Component\AuthorizationComponent;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use MeCms\Controller\Admin\AppController;
use MeCms\TestSuite\Admin\ControllerTestCase;

/**
 * AppControllerTest class
 * @group admin-controller
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * @var \MeCms\Controller\Admin\AppController&\PHPUnit\Framework\MockObject\MockObject
     */
    protected AppController $Controller;

    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!isset($this->Controller)) {
            /** @var \MeCms\Controller\Admin\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
            $Controller = $this->createPartialMockForAbstractClass(AppController::class, ['initialize']);
            $this->Controller = $Controller;
        }
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\AppController::beforeFilter()
     */
    public function testBeforeFilter(): void
    {
        Configure::write('MeCms.admin.records', 7);
        $this->Controller->Authorization = $this->createPartialMock(AuthorizationComponent::class, ['authorize']);
        $this->Controller->dispatchEvent('Controller.initialize');
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $this->Controller->paginate);
        $this->assertEquals('MeCms.View/Admin/App', $this->Controller->viewBuilder()->getClassName());

        /**
         * This tests that the parent `beforeFilter()` method is being executed correctly
         */
        //Whether the user has been reported as a spammer
        $Request = $this->createPartialMock(ServerRequest::class, ['is']);
        $Request->method('is')->willReturnCallback(fn($type) => $type == 'spammer');
        $this->Controller->setRequest($Request);
        /** @var \Cake\Http\Response $Response */
        $Response = $this->Controller->dispatchEvent('Controller.initialize')->getResult();
        $this->_response = $Response;
        $this->assertRedirect(['_name' => 'ipNotAllowed']);
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\AppController::beforeRender()
     */
    public function testBeforeRender(): void
    {
        $url = ['controller' => 'Pages', 'action' => 'index'] + $this->url;
        $this->get(['action' => 'add'] + $url);
        $this->assertSessionNotHasKey('referer');

        //No referer on session for `post` and `put` requests
        foreach (['post', 'put'] as $method) {
            $this->configRequest(['environment' => ['HTTP_REFERER' => Router::url($url, true)]]);
            $this->$method(['action' => 'add'] + $url);
            $this->assertSessionNotHasKey('referer');
        }

        $this->configRequest(['environment' => ['HTTP_REFERER' => Router::url($url, true)]]);
        $this->get(['action' => 'add'] + $url);
        $this->assertSession(Router::url($url), 'referer');

        //No referer when this matches with the current request target
        $this->configRequest(['environment' => ['HTTP_REFERER' => Router::url($url, true)]]);
        $this->get($url);
        $this->assertSessionNotHasKey('referer');
    }

    /**
     * @test
     * @uses \MeCms\Controller\Admin\AppController::redirectMatchingReferer()
     */
    public function testRedirectMatchingReferer(): void
    {
        $this->_response = $this->Controller->redirectMatchingReferer('/');
        $this->assertRedirect('/');

        $url = ['controller' => 'Pages', 'action' => 'index'] + $this->url;
        $this->_response = $this->Controller->setResponse(new Response())->redirectMatchingReferer($url);
        $this->assertRedirect(Router::url($url));

        //Sets a referer with a query string. The referer will match the requested redirect
        $this->Controller->getRequest()->getSession()->write('referer', Router::url($url + ['?' => ['page' => '2']]));
        $this->_response = $this->Controller->setResponse(new Response())->redirectMatchingReferer($url);
        $this->assertRedirect(Router::url($url + ['?' => ['page' => '2']]));

        //Different controller, so the referer does not match the requested redirect
        $this->_response = $this->Controller->setResponse(new Response())->redirectMatchingReferer(['controller' => 'Posts'] + $url);
        $this->assertRedirect(Router::url(['controller' => 'Posts'] + $url));
    }
}
