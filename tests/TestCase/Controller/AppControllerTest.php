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
use Cake\Http\ServerRequest;
use Cake\ORM\Association\BelongsTo;
use MeCms\Controller\PostsController;
use MeCms\Model\Table\PostsTable;
use MeCms\TestSuite\ControllerTestCase;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * Tests autoload modelClass
     * @test
     */
    public function testTableAutoload()
    {
        $Request = new ServerRequest(['params' => ['plugin' => 'MeCms']]);
        $PostsController = new PostsController($Request);
        $this->assertInstanceOf(PostsTable::class, $PostsController->Posts);
        $this->assertInstanceOf(BelongsTo::class, $PostsController->Categories);
        $this->assertInstanceOf(BelongsTo::class, $PostsController->Users);

        $this->expectNotice();
        $this->expectExceptionMessageMatches('/^Undefined property\: PostsController\:\:\$Foo in/');
        $PostsController->Foo;
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter()
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
        $this->_response = $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertRedirect(['_name' => 'offline']);
    }

    /**
     * Tests for `getPaging()` and `setPaging()` methods
     * @test
     */
    public function testGetAndSetPaging()
    {
        $this->assertSame([], $this->Controller->getPaging());
        $this->Controller->setPaging(['paging-example']);
        $this->assertSame(['paging-example'], $this->Controller->getPaging());
        $this->assertSame(['paging-example'], $this->Controller->getRequest()->getAttribute('paging'));
        $this->assertSame(['paging-example'], $this->Controller->getRequest()->getParam('paging'));
    }

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized()
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
