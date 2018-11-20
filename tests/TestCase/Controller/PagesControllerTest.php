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
use Cake\ORM\Entity;
use MeCms\Core\Plugin;
use MeCms\Model\Entity\Page;
use MeCms\TestSuite\ControllerTestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends ControllerTestCase
{
    /**
     * Cache keys to clear for each test
     * @var array
     */
    protected $cacheToClear = ['static_pages'];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Pages',
        'plugin.me_cms.PagesCategories',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        Plugin::load('TestPlugin');

        parent::setUp();
    }

    /**
     * Called after every test method
     * @return void
     */
    public function tearDown()
    {
        Plugin::unload('TestPlugin');

        parent::tearDown();
    }

    /**
     * Tests for `view()` method
     * @test
     */
    public function testView()
    {
        $slug = $this->Table->find('active')->extract('slug')->first();

        $this->get(['_name' => 'page', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Pages/view.ctp');
        $this->assertInstanceof(Page::class, $this->viewVariable('page'));

        $cache = Cache::read(sprintf('view_%s', md5($slug)), $this->Table->getCacheName());
        $this->assertEquals($this->viewVariable('page'), $cache->first());
    }

    /**
     * Tests for `view()` method, with a static page
     * @test
     */
    public function testViewWithStaticPage()
    {
        $this->get(['_name' => 'page', 'page-from-app']);
        $this->assertResponseOk();
        $this->assertResponseContains('This is a static page');
        $this->assertTemplate('StaticPages/page-from-app.ctp');

        $pageFromView = $this->viewVariable('page');
        $this->assertInstanceof(Entity::class, $pageFromView);
        $this->assertInstanceof(Entity::class, $pageFromView->category);
        $this->assertEquals([
            'category' => ['slug' => null, 'title' => null],
            'title' => 'Page From App',
            'subtitle' => null,
            'slug' => 'page-from-app',
        ], $pageFromView->toArray());
    }

    /**
     * Tests for `view()` method, with a static page from a plugin
     * @test
     */
    public function testViewWithStaticPageFromPlugin()
    {
        $this->get(['_name' => 'page', 'test-from-plugin']);
        $this->assertResponseOk();
        $this->assertResponseContains('This is a static page from a plugin');
        $this->assertTemplate('Plugin/TestPlugin/src/Template/StaticPages/test-from-plugin.ctp');
    }

    /**
     * Tests for `preview()` method
     * @test
     */
    public function testPreview()
    {
        $this->setUserGroup('user');
        $slug = $this->Table->find('pending')->extract('slug')->first();

        $this->get(['_name' => 'pagesPreview', $slug]);
        $this->assertResponseOkAndNotEmpty();
        $this->assertTemplate('Pages/view.ctp');
        $this->assertInstanceof(Page::class, $this->viewVariable('page'));
    }
}
