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
namespace MeCms\Test\TestCase\View\Cell;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;
use MeTools\TestSuite\TestCase;

/**
 * PagesWidgetsCellTest class
 */
class PagesWidgetsCellTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PagesTable
     */
    protected $Pages;

    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected $Widget;

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
        Cache::clearAll();

        $this->Pages = TableRegistry::get(ME_CMS . '.Pages');

        $this->Widget = new WidgetHelper(new View);
    }

    /**
     * Test for `categories()` method
     * @test
     */
    public function testCategories()
    {
        $widget = ME_CMS . '.Pages::categories';

        $result = $this->Widget->widget($widget)->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Pages categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/pages/category/category'],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => 'first-page-category']],
            'First page category (1)',
            '/option',
            ['option' => ['value' => 'sub-sub-page-category']],
            'Sub sub page category (2)',
            '/option',
            '/select',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Renders as list
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Pages categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/pages/category/first-page-category', 'title' => 'First page category']],
            'First page category',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/pages/category/sub-sub-page-category', 'title' => 'Sub sub page category']],
            'Sub sub page category',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $widget = $this->Widget->widget($widget);
        $widget->request = $widget->request->withEnv('REQUEST_URI', Router::url(['_name' => 'pagesCategories']));
        $this->assertEmpty($widget->render());

        //Tests cache
        $fromCache = Cache::read('widget_categories', $this->Pages->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertArrayKeysEqual(['first-page-category', 'sub-sub-page-category'], $fromCache->toArray());
    }

    /**
     * Test for `categories()` method, with no pages
     * @test
     */
    public function testCategoriesNoPages()
    {
        $widget = ME_CMS . '.Pages::categories';

        $this->Pages->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `pages()` method
     * @test
     */
    public function testPages()
    {
        $widget = ME_CMS . '.Pages::pages';

        $result = $this->Widget->widget($widget)->render();
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Pages',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/page/first-page', 'title' => 'First page']],
            'First page',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/page/second-page', 'title' => 'Second page']],
            'Second page',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $widget = $this->Widget->widget($widget);
        $widget->request = $widget->request->withEnv('REQUEST_URI', Router::url(['_name' => 'pagesCategories']));
        $this->assertEmpty($widget->render());

        //Tests cache
        $fromCache = Cache::read('widget_list', $this->Pages->cache);
        $this->assertEquals(2, $fromCache->count());
    }

    /**
     * Test for `pages()` method, with no pages
     * @test
     */
    public function testPagesNoPages()
    {
        $this->Pages->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget(ME_CMS . '.Pages::pages')->render());
    }
}
