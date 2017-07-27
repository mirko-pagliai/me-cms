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

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use MeCms\Core\Plugin;
use MeCms\TestSuite\IntegrationTestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends IntegrationTestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.pages',
        'plugin.me_cms.pages_categories',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Plugin::load('TestPlugin');

        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');

        Cache::clear(false, $this->Pages->cache);
        Cache::clear(false, 'static_pages');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        Plugin::unload('TestPlugin');
    }

    /**
     * Adds additional event spies to the controller/view event manager
     * @param \Cake\Event\Event $event A dispatcher event
     * @param \Cake\Controller\Controller|null $controller Controller instance
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        $controller->viewBuilder()->setLayout(false);

        parent::controllerSpy($event, $controller);
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->Pages->find('active')->extract('slug')->first();

        $this->get(['_name' => 'page', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Pages/view.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertNotEmpty($pageFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);

        $cache = Cache::read(sprintf('view_%s', md5($slug)), $this->Pages->cache);
        $this->assertEquals($pageFromView, $cache->first());
    }

    /**
     * Tests for `view()` method, with a static page
     * @test
     */
    public function testViewWithStaticPage()
    {
        $this->get(['_name' => 'page', 'page-from-app']);
        $this->assertResponseOk();
        $this->assertResponseEquals('This is a static page');
        $this->assertTemplate(APP . 'Template/StaticPages/page-from-app.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertInstanceof('stdClass', $pageFromView);
        $this->assertInstanceof('stdClass', $pageFromView->category);
        $pageFromView->category = (array)$pageFromView->category;
        $pageFromView = (array)$pageFromView;
        $this->assertEquals([
            'category' => ['slug' => null, 'title' => null],
            'title' => 'Page From App',
            'subtitle' => null,
            'slug' => 'page-from-app',
        ], $pageFromView);
    }

    /**
     * Tests for `view()` method, with a static page from a plugin
     * @test
     */
    public function testViewWithStaticPageFromPlugin()
    {
        $this->get(['_name' => 'page', 'test-from-plugin']);
        $this->assertResponseOk();
        $this->assertResponseEquals('This is a static page from a plugin');
        $this->assertTemplate(APP . 'Plugin/TestPlugin/src/Template/StaticPages/test-from-plugin.ctp');
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $this->setUserGroup('user');

        $slug = $this->Pages->find('pending')->extract('slug')->first();

        $this->get(['_name' => 'pagesPreview', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Pages/view.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertNotEmpty($pageFromView);
        $this->assertInstanceof('MeCms\Model\Entity\Page', $pageFromView);
    }
}
