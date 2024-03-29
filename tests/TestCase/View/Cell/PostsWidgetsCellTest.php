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
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;
use MeCms\TestSuite\CellTestCase;

/**
 * PostsWidgetsCellTest class
 */
class PostsWidgetsCellTest extends CellTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.PostsCategories',
    ];

    /**
     * @test
     * @uses \MeCms\View\Cell\PostsWidgetsCell::categories()
     */
    public function testCategories(): void
    {
        $widget = 'MeCms.Posts::categories';

        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Posts categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/category/category'],
            ['div' => ['class' => 'input mb-3 select']],
            'select' => ['name' => 'q', 'class' => 'form-control form-select', 'onchange' => 'sendForm(this)'],
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
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Renders as list
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Posts categories',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/category/first-post-category', 'title' => 'First post category']],
            'First post category',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
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
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $this->assertHtml($expected, $result);

        //Empty on categories index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'postsCategories']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->Widget->getView()->setRequest(new ServerRequest());
        $this->assertEquals(2, Cache::read('widget_categories', $this->Table->getCacheName())->count());

        //With no posts
        $this->Table->deleteAll(['id IS NOT' => null]);
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * @test
     * @uses \MeCms\View\Cell\PostsWidgetsCell::latest()
     */
    public function testLatest(): void
    {
        $widget = 'MeCms.Posts::latest';
        $post = $this->Table->find('active')->all()->last();

        //Tries with a limit of 1
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Latest post',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/' . $post->get('slug'), 'title' => $post->get('title')]],
            $post->get('title'),
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['limit' => 1])->render();
        $this->assertHtml($expected, $result);

        //Tries with a limit of 2
        [$post, $otherPost] = $this->Table->find('active')->orderDesc('created')->limit(2)->toArray();
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Latest 2 posts',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/' . $post->get('slug'), 'title' => $post->get('title')]],
            $post->get('title'),
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/post/' . $otherPost->get('slug'), 'title' => $otherPost->get('title')]],
            $otherPost->get('title'),
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();
        $this->assertHtml($expected, $result);

        //Empty on posts index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'posts']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->Widget->getView()->setRequest(new ServerRequest());

        //Tests cache
        $this->assertEquals(1, Cache::read('widget_latest_1', $this->Table->getCacheName())->count());
        $this->assertEquals(2, Cache::read('widget_latest_2', $this->Table->getCacheName())->count());

        //With no posts
        $this->Table->deleteAll(['id IS NOT' => null]);
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
    }

    /**
     * @test
     * @uses \MeCms\View\Cell\PostsWidgetsCell::months()
     */
    public function testMonths(): void
    {
        $widget = 'MeCms.Posts::months';

        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Posts by month',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/' . date('Y/m')],
            ['div' => ['class' => 'input mb-3 select']],
            'select' => ['name' => 'q', 'class' => 'form-control form-select', 'onchange' => 'sendForm(this)'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => '2016/12']],
            'December 2016 (5)',
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
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Renders as list
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Posts by month',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/posts/2016/12', 'title' => 'December 2016']],
            'December 2016',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
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
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $this->assertHtml($expected, $result);

        //Empty on posts index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'posts']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->Widget->getView()->setRequest(new ServerRequest());

        //Tests cache
        $fromCache = Cache::read('widget_months', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        foreach ($fromCache as $key => $month) {
            $this->assertInstanceOf(FrozenTime::class, $month['created']);
            $this->assertEquals($key, $month['created']->i18nFormat('yyyy/MM'));
            $this->assertGreaterThanOrEqual(1, $month['post_count']);
        }

        //With no posts
        $this->Table->deleteAll(['id IS NOT' => null]);
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * @test
     * @uses \MeCms\View\Cell\PostsWidgetsCell::search()
     */
    public function testSearch(): void
    {
        $widget = 'MeCms.Posts::search';

        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Search posts',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => [
                'method' => 'get',
                'accept-charset' => 'utf-8',
                'action' => '/posts/search',
            ],
            ['div' => ['class' => 'input mb-3 text']],
            ['div' => ['class' => 'input-group']],
            'input' => [
                'type' => 'text',
                'name' => 'p',
                'aria-label' => 'Search...',
                'class' => 'form-control',
                'placeholder' => 'Search...',
            ],
            'button' => ['class' => 'btn btn-primary', 'type' => 'submit'],
            'i' => ['class' => 'fas fa-search'],
            ' ',
            '/i',
            '/button',
            '/div',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Empty on search
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'postsSearch']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
    }
}
