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
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use InvalidArgumentException;
use MeCms\TestSuite\CellTestCase;

/**
 * PostsTagsWidgetsCellTest class
 */
class PostsTagsWidgetsCellTest extends CellTestCase
{
    /**
     * @var array
     */
    protected $example = [
        'limit' => 2,
        'prefix' => '#',
        'render' => 'cloud',
        'shuffle' => false,
        'style' => [
            'maxFont' => 40,
            'minFont' => 12,
        ],
    ];

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Tags',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        $this->Table = $this->Table ?: TableRegistry::getTableLocator()->get('MeCms.Tags');

        parent::setUp();
    }

    /**
     * Test for `getFontSizes()` method
     * @test
     */
    public function testGetFontSizes(): void
    {
        $widget = $this->Widget->widget('MeCms.PostsTags::popular');
        $getFontSizesMethod = function ($style) use ($widget) {
            return $this->invokeMethod($widget, 'getFontSizes', [$style]);
        };

        $this->assertEquals([40, 12], $getFontSizesMethod([]));
        $this->assertEquals([20, 12], $getFontSizesMethod(['maxFont' => 20]));
        $this->assertEquals([40, 20], $getFontSizesMethod(['minFont' => 20]));
        $this->assertEquals([30, 20], $getFontSizesMethod(['maxFont' => 30, 'minFont' => 20]));
        $this->assertEquals([40, 12], $getFontSizesMethod(false));

        //With invalid values
        $this->expectException(InvalidArgumentException::class);
        $getFontSizesMethod(['maxFont' => 10, 'minFont' => 20]);
    }

    /**
     * Test for `popular()` method
     * @test
     */
    public function testPopular(): void
    {
        $widget = 'MeCms.PostsTags::popular';

        //Tries using the style (`maxFont` and `minFont`)
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
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
        $this->assertHtml($expected, $this->Widget->widget($widget, $this->example)->render());

        //Tries with a custom prefix
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
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
        $result = $this->Widget->widget($widget, array_merge($this->example, [
            'prefix' => '-',
            'style' => false,
        ]))->render();
        $this->assertHtml($expected, $result);

        //Tries to render as form
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/tag/tag'],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'sendForm(this)', 'class' => 'form-control'],
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
        $result = $this->Widget->widget($widget, array_merge($this->example, [
            'render' => 'form',
            'style' => false,
        ]))->render();
        $this->assertHtml($expected, $result);

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/posts/tag/cat', 'title' => 'cat']],
            'cat',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
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
        //Tries to render as list
        $result = $this->Widget->widget($widget, array_merge($this->example, [
            'render' => 'list',
            'style' => false,
        ]))->render();
        $this->assertHtml($expected, $result);

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
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
        //Tries with shuffle
        $result = $this->Widget->widget($widget, array_merge($this->example, [
            'shuffle' => true,
            'style' => false,
        ]))->render();
        $this->assertHtml($expected, $result);

        //Empty on tags index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'postsTags']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->Widget->getView()->setRequest(new ServerRequest());

        //Tests cache
        $fromCache = Cache::read('widget_tags_popular_2', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        array_map([$this, 'assertNull'], $fromCache->extract('size')->toArray());

        $fromCache = Cache::read('widget_tags_popular_2_max_40_min_12', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        array_map([$this, 'assertNotEmpty'], $fromCache->extract('size')->toArray());

        //With no tags
        $this->Table->deleteAll(['id IS NOT' => null]);
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget, $this->example)->render());
        $this->assertEmpty($this->Widget->widget($widget, array_merge($this->example, ['render' => 'form']))->render());
        $this->assertEmpty($this->Widget->widget($widget, array_merge($this->example, ['render' => 'list']))->render());
        $this->assertEmpty($this->Widget->widget($widget, array_merge($this->example, ['shuffle' => true]))->render());
    }

    /**
     * Test for `popular()` method, with tags that have the same `post_count`
     *  value
     * @test
     */
    public function testPopularWithTagsSamePostCount(): void
    {
        $widget = 'MeCms.PostsTags::popular';

        //Adds some tag, with the same `post_count`
        foreach ([
            ['tag' => 'example1', 'post_count' => 999],
            ['tag' => 'example2', 'post_count' => 999],
        ] as $data) {
            $entity = $this->Table->newEntity($data, ['accessibleFields' => ['post_count' => true]]);
            $this->assertNotFalse($this->Table->save($entity));
        }

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
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
        $this->assertHtml($expected, $this->Widget->widget($widget, $this->example)->render());

        //Tests cache
        $fromCache = Cache::read('widget_tags_popular_2_max_40_min_12', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals(['example1' => 40, 'example2' => 40], $fromCache->extract('size')->toArray());
    }
}
