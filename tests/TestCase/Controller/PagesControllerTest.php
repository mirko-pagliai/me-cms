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

use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Cake\Routing\Router;
use MeCms\Model\Entity\Page;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends ControllerTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Tests for `view()` method
     * @uses \MeCms\Controller\PagesController::view()
     * @test
     */
    public function testView(): void
    {
        $this->get(['_name' => 'page', 'first-page']);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Pages' . DS . 'view.php');
        $this->assertInstanceOf(Page::class, $this->viewVariable('page'));
        $cache = Cache::read('view_' . md5('first-page'), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('page'), $cache->first());
    }

    /**
     * Tests for `view()` method, with a static page
     * @uses \MeCms\Controller\PagesController::view()
     * @test
     */
    public function testViewWithStaticPage(): void
    {
        Cache::clear('static_pages');
        $slug = 'page-from-app';
        $url = ['_name' => 'page', $slug];
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('This is a static page');
        $this->assertTemplate('StaticPages' . DS . $slug . '.php');
        $this->assertInstanceOf(Entity::class, $this->viewVariable('page'));
        $this->assertEquals([
            'title' => 'Page From App',
            'url' => Router::url($url, true),
        ] + compact('slug'), $this->viewVariable('page')->toArray());
    }

    /**
     * Tests for `view()` method, with a static page from a plugin
     * @uses \MeCms\Controller\PagesController::view()
     * @test
     */
    public function testViewWithStaticPageFromPlugin(): void
    {
        Cache::clear('static_pages');
        $this->loadPlugins(['TestPlugin' => []]);
        $this->get(['_name' => 'page', 'test-from-plugin']);
        $this->assertResponseOk();
        $this->assertResponseContains('This is a static page from a plugin');
        $this->assertTemplate('Plugin' . DS . 'TestPlugin' . DS . 'templates' . DS . 'StaticPages' . DS . 'test-from-plugin.php');
    }

    /**
     * Tests for `preview()` method
     * @uses \MeCms\Controller\PagesController::preview()
     * @test
     */
    public function testPreview(): void
    {
        $url = $this->url + ['action' => 'preview', 'disabled-page'];

        $this->get($url);
        $this->assertResponseCode(302);
        $this->assertStringStartsWith('/login', $this->_response->getHeader('Location')[0]);

        $this->setAuthData();
        $this->get($url);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Pages' . DS . 'view.php');
        $this->assertInstanceOf(Page::class, $this->viewVariable('page'));
    }
}
