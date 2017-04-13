<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;
use MeCms\Core\Plugin;
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends IntegrationTestCase
{
    use AuthMethodsTrait;

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

        $this->Pages = TableRegistry::get('MeCms.Pages');

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

        unset($this->Pages);
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
        $slug = 'first-page';

        $this->get(['_name' => 'page', $slug]);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Pages/view.ctp');

        $viewVariable = $this->viewVariable('page');
        $this->assertInstanceof('MeCms\Model\Entity\Page', $viewVariable);

        $cache = Cache::read(sprintf('view_%s', md5($slug)), $this->Pages->cache);
        $this->assertEquals($viewVariable, $cache->first());
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

        $viewVariable = $this->viewVariable('page');
        $this->assertInstanceof('stdClass', $viewVariable);
        $this->assertInstanceof('stdClass', $viewVariable->category);
        $viewVariable->category = (array)$viewVariable->category;
        $viewVariable = (array)$viewVariable;
        $this->assertEquals([
            'category' => ['slug' => null, 'title' => null],
            'title' => 'Page From App',
            'subtitle' => null,
            'slug' => 'page-from-app',
        ], $viewVariable);
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
        $slug = $this->Pages->find('pending')->extract('slug')->first();
        $url = ['_name' => 'pagesPreview', $slug];

        $this->get($url);
        $this->assertRedirect(['_name' => 'login', '?' => ['redirect' => Router::url($url)]]);

        $this->setUserGroup('user');

        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTemplate(ROOT . 'src/Template/Pages/view.ctp');

        $viewVariable = $this->viewVariable('page');
        $this->assertInstanceof('MeCms\Model\Entity\Page', $viewVariable);
    }
}
