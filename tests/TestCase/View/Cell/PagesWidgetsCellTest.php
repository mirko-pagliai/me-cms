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

namespace MeCms\Test\TestCase\View\Cell;

use Cake\Cache\Cache;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use MeCms\TestSuite\CellTestCase;

/**
 * PagesWidgetsCellTest class
 */
class PagesWidgetsCellTest extends CellTestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Test for `categories()` method
     * @test
     */
    public function testCategories(): void
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
            'select' => ['name' => 'q', 'onchange' => 'sendForm(this)', 'class' => 'form-control'],
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
        $this->assertHtml($expected, $this->Widget->widget($widget)->render());

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
        $this->assertHtml($expected, $this->Widget->widget($widget, ['render' => 'list'])->render());

        //Empty on categories index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'pagesCategories']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->Widget->getView()->setRequest(new ServerRequest());
        $this->assertEquals(2, Cache::read('widget_categories', $this->Table->getCacheName())->count());

        //With no pages
        $this->Table->deleteAll(['id IS NOT' => null]);
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `pages()` method
     * @test
     */
    public function testPages(): void
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
        $this->assertHtml($expected, $this->Widget->widget($widget)->render());

        //Empty on categories index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'pagesCategories']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->Widget->getView()->setRequest(new ServerRequest());
        $this->assertEquals(2, Cache::read('widget_list', $this->Table->getCacheName())->count());

        //With no pages
        $this->Table->deleteAll(['id IS NOT' => null]);
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
    }
}
