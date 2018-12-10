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
use Cake\Routing\Router;
use MeCms\Model\Table\PagesTable;
use MeCms\TestSuite\CellTestCase;

/**
 * PagesWidgetsCellTest class
 */
class PagesWidgetsCellTest extends CellTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Table;

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
    public function setUp()
    {
        parent::setUp();

        Cache::clearAll();

        $this->Table = $this->getMockForTable(PagesTable::class, null);
    }

    /**
     * Test for `categories()` method
     * @test
     */
    public function testCategories()
    {
        $widget = 'MeCms.Pages::categories';

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
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Renders as list
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
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $result = $this->Widget->widget($widget);
        $result->request = $result->request->withEnv('REQUEST_URI', Router::url(['_name' => 'pagesCategories']));
        $this->assertEmpty($result->render());

        //Tests cache
        $fromCache = Cache::read('widget_categories', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        $this->assertArrayKeysEqual(['first-page-category', 'sub-sub-page-category'], $fromCache->toArray());

        //With no pages
        Cache::clearAll();
        $this->Table->deleteAll(['id >=' => 1]);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `pages()` method
     * @test
     */
    public function testPages()
    {
        $widget = 'MeCms.Pages::pages';

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
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $result = $this->Widget->widget($widget);
        $result->request = $result->request->withEnv('REQUEST_URI', Router::url(['_name' => 'pagesCategories']));
        $this->assertEmpty($result->render());

        //Tests cache
        $fromCache = Cache::read('widget_list', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());

        //With no pages
        Cache::clearAll();
        $this->Table->deleteAll(['id >=' => 1]);
        $this->assertEmpty($this->Widget->widget($widget)->render());
    }
}
