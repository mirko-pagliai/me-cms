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

namespace MeCms\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Routing\Router;
use MeCms\TestSuite\ControllerTestCase;

/**
 * AppControllerTest class
 */
class AppControllerTest extends ControllerTestCase
{
    /**
     * Controller instance
     * @var \MeCms\Controller\Admin\AppController&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $Controller;

    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Controller->setRequest($this->Controller->getRequest()->withParam('prefix', ADMIN_PREFIX));
    }

    /**
     * Tests for `beforeFilter()` method
     * @test
     */
    public function testBeforeFilter(): void
    {
        parent::testBeforeFilter();

        Configure::write('MeCms.admin.records', 7);

        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEmpty($this->Controller->Auth->allowedActions);
        $this->assertEquals(['limit' => 7, 'maxLimit' => 7], $this->Controller->paginate);
        $this->assertEquals('MeCms.View/Admin', $this->Controller->viewBuilder()->getClassName());

        //Ajax request
        $this->Controller->setRequest($this->Controller->getRequest()->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest'));
        $this->Controller->beforeFilter(new Event('myEvent'));
        $this->assertEquals('MeCms.ajax', $this->Controller->viewBuilder()->getLayout());

        //If the site is offline this makes a redirect
        //This works anyway, because the admin interface never goes offline
        Configure::write('MeCms.default.offline', true);
        $this->Controller->getRequest()->clearDetectorCache();
        $this->assertNull($this->Controller->beforeFilter(new Event('myEvent')));

        $url = ['controller' => 'Pages', 'action' => 'index'] + $this->url;
        $this->loadFixtures();

        $this->get(['action' => 'add'] + $url);
        $this->assertSessionNotHasKey('referer');

        $this->configRequest(['environment' => ['HTTP_REFERER' => Router::url($url, true)]]);
        $this->get(['action' => 'add'] + $url);
        $this->assertSession(Router::url($url), 'referer');

        //No referer when this matches with the current request target
        $this->configRequest(['environment' => ['HTTP_REFERER' => Router::url($url, true)]]);
        $this->get($url);
        $this->assertSessionNotHasKey('referer');

        //No referer on session for `post` and `put` requests
        foreach (['post', 'put'] as $method) {
            $this->configRequest(['environment' => ['HTTP_REFERER' => Router::url($url, true)]]);
            $this->$method(['action' => 'add'] + $url);
            $this->assertSessionNotHasKey('referer');
        }
    }

    /**
     * Tests for `redirectMatchingReferer()` method
     * @test
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

    /**
     * Tests for `isAuthorized()` method
     * @test
     */
    public function testIsAuthorized(): void
    {
        $this->assertGroupsAreAuthorized([
            'admin' => true,
            'manager' => true,
            'user' => false,
        ]);
    }
}
