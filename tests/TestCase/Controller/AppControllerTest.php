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
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Association\BelongsTo;
use MeCms\Controller\PostsController;
use MeCms\Model\Table\PostsTable;
use MeCms\TestSuite\ControllerTestCase;
use RuntimeException;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * Tests autoload modelClass
     * @test
     */
    public function testTableAutoload(): void
    {
        $Request = new ServerRequest(['params' => ['plugin' => 'MeCms']]);
        $PostsController = new PostsController($Request);
        $this->assertInstanceOf(PostsTable::class, $PostsController->Posts);
        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(BelongsTo::class, $PostsController->Categories);
        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(BelongsTo::class, $PostsController->Users);

        $this->expectNotice();
        $this->expectExceptionMessageMatches('/^Undefined property\: PostsController\:\:\$Foo in/');
        /* @phpstan-ignore-next-line */
        $PostsController->Foo;
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter(): void
    {
        parent::testBeforeFilter();

        Configure::write('MeCms.default.records', 5);

        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertNotEmpty($this->Controller->Auth->allowedActions);
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
        $this->_response = $this->Controller->beforeFilter(new Event('myEvent')) ?: new Response();
        $this->assertRedirect(['_name' => 'offline']);
    }

    /**
     * Tests for `getPaging()` and `setPaging()` methods
     * @test
     */
    public function testGetAndSetPaging(): void
    {
        $this->assertSame([], $this->Controller->getPaging());
        $this->Controller->setPaging(['paging-example']);
        $this->assertSame(['paging-example'], $this->Controller->getPaging());
        $this->assertSame(['paging-example'], $this->Controller->getRequest()->getAttribute('paging'));
        $this->assertSame(['paging-example'], $this->Controller->getRequest()->getParam('paging'));
    }

    /**
     * Tests for `initialize()` method, for `Recaptcha` component
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
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized(): void
    {
        //With prefixes
        foreach ([
            null => true,
            ADMIN_PREFIX => false,
            'otherPrefix' => false,
        ] as $prefix => $expected) {
            $request = $this->Controller->getRequest()->withParam('prefix', $prefix);
            $request->clearDetectorCache();
            $this->assertSame($expected, $this->Controller->setRequest($request)->isAuthorized());
        }
    }
}
