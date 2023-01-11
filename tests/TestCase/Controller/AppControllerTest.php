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
use Cake\Event\Event;
use MeCms\TestSuite\ControllerTestCase;
use RuntimeException;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * Tests for `beforeFilter()` method
     * @uses \MeCms\Controller\AppController::beforeFilter()
     * @test
     */
    public function testBeforeFilter(): void
    {
        parent::testBeforeFilter();

        Configure::write('MeCms.default.records', 5);

        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEquals(['limit' => 5, 'maxLimit' => 5], $this->Controller->paginate);
        $this->assertNull($this->Controller->viewBuilder()->getLayout());
        $this->assertEquals('MeCms.View/App', $this->Controller->viewBuilder()->getClassName());

        //Ajax request
        $this->Controller->setRequest($this->Controller->getRequest()->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest'));
        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEquals('MeCms.ajax', $this->Controller->viewBuilder()->getLayout());

        //If the site is offline this makes a redirect
        Configure::write('MeCms.default.offline', true);
        $this->Controller->getRequest()->clearDetectorCache();
        $this->get('/');
        $this->assertRedirect(['_name' => 'offline']);
    }

    /**
     * Tests for `getPaging()` and `setPaging()` methods
     * @uses \MeCms\Controller\AppController::getPaging()
     * @test
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
     * Tests for `initialize()` method, for `Recaptcha` component
     * @uses \MeCms\Controller\AppController::initialize()
     * @test
     */
    public function testInitializeForRecaptchaComponent(): void
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
