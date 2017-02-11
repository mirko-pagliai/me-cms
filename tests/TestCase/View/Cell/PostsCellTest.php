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
namespace MeCms\Test\TestCase\View\Cell;

use Cake\Cache\Cache;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;

/**
 * PostsCellTest class
 */
class PostsCellTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable
     */
    protected $Posts;

    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected $Widget;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts',
        'plugin.me_cms.posts_categories',
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

        $this->Posts = TableRegistry::get('MeCms.Posts');

        $this->Widget = new WidgetHelper(new View);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Posts, $this->Widget);
    }

    /**
     * Test for `categories()` method
     * @test
     */
    public function testCategories()
    {
        $widget = MECMS . '.Posts::categories';

        $result = $this->Widget->widget($widget)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Posts categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/category/category'],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => 'first-post-category']],
            'First post category (1)',
            '/option',
            ['option' => ['value' => 'sub-sub-post-category']],
            'Sub sub post category (2)',
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
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Posts categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/category/first-post-category', 'title' => 'First post category']],
            'First post category',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/category/sub-sub-post-category', 'title' => 'Sub sub post category']],
            'Sub sub post category',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $request = new Request(Router::url(['_name' => 'postsCategories']));
        $this->Widget = new WidgetHelper(new View($request));
        $result = $this->Widget->widget($widget)->render();
        $this->assertEmpty($result);

        //Tests cache
        $fromCache = Cache::read('widget_categories', $this->Posts->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals([
            'first-post-category',
            'sub-sub-post-category',
        ], array_keys($fromCache->toArray()));
    }

    /**
     * Test for `latest()` method
     * @test
     */
    public function testLatest()
    {
        $widget = MECMS . '.Posts::latest';

        //Tries with a limit of 1
        $result = $this->Widget->widget($widget, ['limit' => 1])->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Latest post',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/third-post', 'title' => 'Third post']],
            'Third post',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries with a limit of 2
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Latest 2 posts',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/third-post', 'title' => 'Third post']],
            'Third post',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/second-post', 'title' => 'Second post']],
            'Second post',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on posts index
        $request = new Request(Router::url(['_name' => 'posts']));
        $this->Widget = new WidgetHelper(new View($request));
        $result = $this->Widget->widget($widget)->render();
        $this->assertEmpty($result);

        //Tests cache
        $fromCache = Cache::read('widget_latest_1', $this->Posts->cache);
        $this->assertEquals(1, $fromCache->count());

        $fromCache = Cache::read('widget_latest_2', $this->Posts->cache);
        $this->assertEquals(2, $fromCache->count());
    }

    /**
     * Test for `months()` method
     * @test
     */
    public function testMonths()
    {
        $widget = MECMS . '.Posts::months';

        $result = $this->Widget->widget($widget)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Posts by month',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/' . date('Y/m')],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => '2016/12']],
            'December 2016 (2)',
            '/option',
            ['option' => ['value' => '2016/11']],
            'November 2016 (1)',
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
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Posts by month',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/2016/12', 'title' => 'December 2016']],
            'December 2016',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/2016/11', 'title' => 'November 2016']],
            'November 2016',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on posts index
        $request = new Request(Router::url(['_name' => 'posts']));
        $this->Widget = new WidgetHelper(new View($request));
        $result = $this->Widget->widget($widget)->render();
        $this->assertEmpty($result);

        //Tests cache
        $fromCache = Cache::read('widget_months', $this->Posts->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals([
            '2016/12',
            '2016/11',
        ], array_keys($fromCache->toArray()));

        foreach ($fromCache as $key => $entity) {
            $this->assertInstanceOf('Cake\I18n\FrozenDate', $entity->month);
            $this->assertEquals($key, $entity->month->i18nFormat('yyyy/MM'));
        }
    }

    /**
     * Test for `search()` method
     * @test
     */
    public function testSearch()
    {
        $widget = MECMS . '.Posts::search';

        $result = $this->Widget->widget($widget)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Search posts',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'class' => 'form-inline', 'action' => '/posts/search'],
            ['div' => ['class' => 'input form-group text']],
            ['div' => ['class' => 'input-group']],
            'input' => ['type' => 'text', 'name' => 'p', 'placeholder' => 'Search...', 'class' => 'form-control'],
            'span' => ['class' => 'input-group-btn'],
            'button' => ['class' => 'btn-primary btn', 'type' => 'button'],
            'i' => ['class' => 'fa fa-search'],
            ' ',
            '/i',
            '/button',
            '/span',
            '/div',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on search
        $request = new Request(Router::url(['_name' => 'postsSearch']));
        $this->Widget = new WidgetHelper(new View($request));
        $result = $this->Widget->widget($widget)->render();
        $this->assertEmpty($result);
    }
}
