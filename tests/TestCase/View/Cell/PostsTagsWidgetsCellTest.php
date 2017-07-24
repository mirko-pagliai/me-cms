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
use Cake\TestSuite\TestCase;
use MeCms\View\Cell\PostsTagsWidgetsCell;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;
use Reflection\ReflectionTrait;

/**
 * PostsTagsWidgetsCellTest class
 */
class PostsTagsWidgetsCellTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\View\Cell\PostsTagsWidgetsCell
     */
    protected $PostsTagsWidgetsCell;

    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Tags;

    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected $Widget;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.tags',
    ];

    /**
     * Default options
     * @var array
     */
    protected $options;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Cache::clearAll();

        $this->PostsTagsWidgetsCell = new PostsTagsWidgetsCell;

        $this->Tags = TableRegistry::get(ME_CMS . '.Tags');

        $this->Widget = new WidgetHelper(new View);

        $this->options = [
            'limit' => 2,
            'prefix' => '#',
            'render' => 'cloud',
            'shuffle' => false,
            'style' => [
                'maxFont' => 40,
                'minFont' => 12,
            ],
        ];
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->PostsTagsWidgetsCell, $this->Tags, $this->Widget, $this->options);
    }

    /**
     * Test for `_getFontSizes()` method
     * @test
     */
    public function testGetFontSizes()
    {
        $result = $this->invokeMethod($this->PostsTagsWidgetsCell, '_getFontSizes', [[]]);
        $this->assertEquals([40, 12], $result);

        $result = $this->invokeMethod($this->PostsTagsWidgetsCell, '_getFontSizes', [['maxFont' => 20]]);
        $this->assertEquals([20, 12], $result);

        $result = $this->invokeMethod($this->PostsTagsWidgetsCell, '_getFontSizes', [['minFont' => 20]]);
        $this->assertEquals([40, 20], $result);

        $result = $this->invokeMethod($this->PostsTagsWidgetsCell, '_getFontSizes', [['maxFont' => 30, 'minFont' => 20]]);
        $this->assertEquals([30, 20], $result);
    }

    /**
     * Test for `_getFontSizes()` method, with invalid values
     * @expectedException Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Invalid values
     * @test
     */
    public function testGetFontSizesWithInvalidValues()
    {
        $this->invokeMethod($this->PostsTagsWidgetsCell, '_getFontSizes', [['maxFont' => 10, 'minFont' => 20]]);
    }

    /**
     * Test for `popular()` method
     * @test
     */
    public function testPopular()
    {
        $widget = ME_CMS . '.PostsTags::popular';

        //Tries using the style (`maxFont` and `minFont`)
        $result = $this->Widget->widget($widget, $this->options)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['div' => true],
            ['a' => ['href' => '/posts/tag/cat', 'style' => 'font-size:40px;', 'title' => 'cat']],
            '#cat',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => '/posts/tag/dog', 'style' => 'font-size:12px;', 'title' => 'dog']],
            '#dog',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries with a custom prefix
        $result = $this->Widget->widget($widget, am($this->options, [
            'prefix' => '-',
            'style' => false,
        ]))->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['div' => true],
            ['a' => ['href' => '/posts/tag/cat', 'title' => 'cat']],
            '-cat',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => '/posts/tag/dog', 'title' => 'dog']],
            '-dog',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries to render as form
        $result = $this->Widget->widget($widget, am($this->options, [
            'render' => 'form',
            'style' => false,
        ]))->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/tag/tag'],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => 'cat']],
            'cat (4)',
            '/option',
            ['option' => ['value' => 'dog']],
            'dog (2)',
            '/option',
            '/select',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries to render as list
        $result = $this->Widget->widget($widget, am($this->options, [
            'render' => 'list',
            'style' => false,
        ]))->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/posts/tag/cat', 'title' => 'cat']],
            'cat',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/posts/tag/dog', 'title' => 'dog']],
            'dog',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries with shuffle
        $result = $this->Widget->widget($widget, am($this->options, [
            'shuffle' => true,
            'style' => false,
        ]))->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['div' => true],
            ['a' => ['href' => 'preg:/\/posts\/tag\/(cat|dog)/', 'title' => 'preg:/(cat|dog)/']],
            'preg:/#(cat|dog)/',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => 'preg:/\/posts\/tag\/(cat|dog)/', 'title' => 'preg:/(cat|dog)/']],
            'preg:/#(cat|dog)/',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on tags index
        $widgetClass = $this->Widget->widget($widget);
        $widgetClass->request->env('REQUEST_URI', Router::url(['_name' => 'postsTags']));
        $this->assertEmpty($widgetClass->render());

        //Tests cache
        $fromCache = Cache::read('widget_tags_popular_2', $this->Tags->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals(['cat', 'dog'], array_keys($fromCache->toArray()));

        foreach ($fromCache as $entity) {
            $this->assertNull($entity->size);
        }

        $fromCache = Cache::read('widget_tags_popular_2_max_40_min_12', $this->Tags->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals(['cat', 'dog'], array_keys($fromCache->toArray()));

        foreach ($fromCache as $entity) {
            $this->assertGreaterThan(0, $entity->size);
        }

        //Deletes all tags
        $this->Tags->deleteAll(['id >=' => 1]);

        //Empty with no tags
        $result = $this->Widget->widget($widget)->render();
        $this->assertEmpty($result);
    }

    /**
     * Test for `popular()` method, with no tags
     * @test
     */
    public function testPopularWithNoTags()
    {
        $widget = ME_CMS . '.PostsTags::popular';

        $this->Tags->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget($widget, $this->options)->render());
        $this->assertEmpty($this->Widget->widget($widget, array_merge($this->options, ['render' => 'form']))->render());
        $this->assertEmpty($this->Widget->widget($widget, array_merge($this->options, ['render' => 'list']))->render());
        $this->assertEmpty($this->Widget->widget($widget, array_merge($this->options, ['shuffle' => true]))->render());
    }

    /**
     * Test for `popular()` method, with tags that have the same `post_count`
     *  value
     * @test
     */
    public function testPopularWithTagsSamePostCount()
    {
        $widget = ME_CMS . '.PostsTags::popular';

        //Adds some tag, with the same `post_count`
        foreach ([
            ['tag' => 'example1', 'post_count' => 999],
            ['tag' => 'example2', 'post_count' => 999],
        ] as $data) {
            $entity = $this->Tags->newEntity($data, ['accessibleFields' => ['post_count' => true]]);
            $this->assertNotFalse($this->Tags->save($entity));
        }

        $result = $this->Widget->widget($widget, $this->options)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['div' => true],
            ['a' => ['href' => '/posts/tag/example1', 'style' => 'font-size:40px;', 'title' => 'example1']],
            '#example1',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => '/posts/tag/example2', 'style' => 'font-size:40px;', 'title' => 'example2']],
            '#example2',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tests cache
        $fromCache = Cache::read('widget_tags_popular_2_max_40_min_12', $this->Tags->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals(['example1', 'example2'], array_keys($fromCache->toArray()));

        foreach ($fromCache as $entity) {
            $this->assertEquals(40, $entity->size);
        }
    }
}
