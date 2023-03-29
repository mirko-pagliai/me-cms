<?php
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

namespace MeCms\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use MeCms\Controller\AppController;
use MeCms\TestSuite\ControllerTestCase;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject
     */
    protected AppController $Controller;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!isset($this->Controller)) {
            /** @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
            $Controller = $this->createPartialMockForAbstractClass($this->originClassName, ['initialize']);
            $this->Controller = $Controller;
        }
    }

    /**
     * @test
     * @uses \MeCms\Controller\AppController::beforeFilter()
     */
    public function testBeforeFilter(): void
    {
        Configure::write('MeCms.default.records', 5);
        $this->Controller->dispatchEvent('Controller.initialize');
        $this->assertEquals(['limit' => 5, 'maxLimit' => 5], $this->Controller->paginate);
        $this->assertEquals('MeCms.View/App', $this->Controller->viewBuilder()->getClassName());

        //If the site is offline
        $Request = $this->createPartialMock(ServerRequest::class, ['is']);
        $Request->method('is')->with($this->equalTo('offline'))->willReturn(true);
        /** @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
        $Controller = $this->createPartialMockForAbstractClass($this->originClassName, ['initialize'], [$Request]);
        /** @var \Cake\Http\Response $Response */
        $Response = $Controller->dispatchEvent('Controller.initialize')->getResult();
        $this->_response = $Response;
        $this->assertRedirect(['_name' => 'offline']);

        //Whether the user has been reported as a spammer
        $Request = $this->createPartialMock(ServerRequest::class, ['is']);
        $Request->method('is')->willReturnCallback(fn(string $type): bool => $type == 'spammer');
        /** @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
        $Controller = $this->createPartialMockForAbstractClass($this->originClassName, ['initialize'], [$Request]);
        /** @var \Cake\Http\Response $Response */
        $Response = $Controller->dispatchEvent('Controller.initialize')->getResult();
        $this->_response = $Response;
        $this->assertRedirect(['_name' => 'ipNotAllowed']);
    }

    /**
     * @test
     * @uses \MeCms\Controller\AppController::beforeRender()
     */
    public function testBeforeRender(): void
    {
        //Layout for ajax and json requests
        $this->Controller->setRequest($this->Controller->getRequest()->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest'));
        $this->Controller->dispatchEvent('Controller.beforeRender');
        $this->assertEquals('MeCms.ajax', $this->Controller->viewBuilder()->getLayout());
    }

    /**
     * @test
     * @uses \MeCms\Controller\AppController::getPaging()
     * @uses \MeCms\Controller\AppController::setPaging()
     */
    public function testGetAndSetPaging(): void
    {
        $this->assertSame([], $this->Controller->getPaging());

        $paging = ['Posts' => ['paging-key' => 'paging-value']];
        $this->Controller->setPaging($paging);
        $this->assertSame($paging, $this->Controller->getPaging());
        $this->assertSame($paging, $this->Controller->getRequest()->getAttribute('paging'));
        $this->assertSame($paging, $this->Controller->getRequest()->getParam('paging'));
    }

    /**
     * @test
     * @uses \MeCms\Controller\AppController::getQueryPage()
     */
    public function testGetQueryPage(): void
    {
        $this->assertSame('1', $this->Controller->getQueryPage());

        $this->Controller->setRequest($this->Controller->getRequest()->withQueryParams(['page' => '2']));
        $this->assertSame('2', $this->Controller->getQueryPage());

        //With invalid values, it's `1`
        foreach ([0, '0', ['key' => 'value'], null, 'string'] as $page) {
            $this->Controller->setRequest($this->Controller->getRequest()->withQueryParams(compact('page')));
            $this->assertSame('1', $this->Controller->getQueryPage());
        }
    }

    /**
     * @test
     * @uses \MeCms\Controller\AppController::initialize()
     */
    public function testInitialize(): void
    {
        /** @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
        $Controller = $this->createPartialMockForAbstractClass($this->originClassName);
        $Controller->initialize();

        $this->assertFalse($Controller->components()->has('Recaptcha'));

        Configure::write('MeCms.security.recaptcha', true);
        Configure::write('Recaptcha', [
            'public' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'private' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
        ]);
        $Controller->initialize();
        $this->assertTrue($Controller->components()->has('Recaptcha'));

        $this->assertCount(1, $Controller->Authentication->getEventManager()->listeners('Authentication.afterIdentify'));
        $this->assertFalse($Controller->Authentication->getConfig('requireIdentity'));

        $this->expectExceptionMessage('Missing Recaptcha keys. You can rename the `config/recaptcha.example.php` file as `recaptcha.php` and change the keys');
        Configure::load('MeCms.recaptcha');
        $Controller->initialize();
    }
}
