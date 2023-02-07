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
use RuntimeException;

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
            $Request = new ServerRequest(['params' => $this->url]);
            /** @var \MeCms\Controller\AppController&\PHPUnit\Framework\MockObject\MockObject $Controller */
            $Controller = $this->getMockForAbstractClass($this->originClassName, [$Request, null, $this->alias]);
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

        //If the site is offline
        Configure::write('MeCms.default.offline', true);
        $this->get('/');
        $this->assertRedirect(['_name' => 'offline']);
        Configure::write('MeCms.default.offline', false);

        $Controller = $this->getMockForAbstractClass(AppController::class, [], '', true, true, true, ['initialize', 'isSpammer']);
        $Controller->method('isSpammer')->willReturnOnConsecutiveCalls(false, true);

        $Controller->dispatchEvent('Controller.initialize');
        $this->assertEquals(['limit' => 5, 'maxLimit' => 5], $Controller->paginate);
        $this->assertEquals('MeCms.View/App', $Controller->viewBuilder()->getClassName());

        //Whether the user has been reported as a spammer
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
        $this->Controller->initialize();

        $this->assertFalse($this->Controller->components()->has('Recaptcha'));

        Configure::write('MeCms.security.recaptcha', true);
        Configure::write('Recaptcha', [
            'public' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'private' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
        ]);
        $this->Controller->initialize();
        $this->assertTrue($this->Controller->components()->has('Recaptcha'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing Recaptcha keys. You can rename the `config/recaptcha.example.php` file as `recaptcha.php` and change the keys');
        Configure::load('MeCms.recaptcha');
        $this->Controller->initialize();

        $this->assertTrue($this->Controller->Authentication->getConfig('requireIdentity'));

        //Tries some actions. They do not require authentication
        foreach (['/posts', '/posts/categories'] as $url) {
            $this->get($url);
            $this->assertResponseOkAndNotEmpty();
        }
    }
}
