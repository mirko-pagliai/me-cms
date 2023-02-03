<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
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
use ErrorException;
use MeCms\Model\Table\TagsTable;
use MeCms\TestSuite\CellTestCase;
use Tools\TestSuite\ReflectionTrait;

/**
 * PostsTagsWidgetsCellTest class
 */
class PostsTagsWidgetsCellTest extends CellTestCase
{
    use ReflectionTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $example = [
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
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Tags',
    ];

    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected TagsTable $Table;

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!isset($this->Table)) {
            /** @var \MeCms\Model\Table\TagsTable $Table */
            $Table = $this->getTable('MeCms.Tags');
            $this->Table = $Table;
        }
    }

    /**
     * @test
     * @uses \MeCms\View\Cell\PostsTagsWidgetsCell::getFontSizes()
     */
    public function testGetFontSizes(): void
    {
        $widget = $this->Widget->widget('MeCms.PostsTags::popular');
        $getFontSizesMethod = fn($style) => $this->invokeMethod($widget, 'getFontSizes', [$style]);

        $this->assertEquals([40, 12], $getFontSizesMethod([]));
        $this->assertEquals([20, 12], $getFontSizesMethod(['maxFont' => 20]));
        $this->assertEquals([40, 20], $getFontSizesMethod(['minFont' => 20]));
        $this->assertEquals([30, 20], $getFontSizesMethod(['maxFont' => 30, 'minFont' => 20]));

        //With invalid values
        $this->expectException(ErrorException::class);
        $getFontSizesMethod(['maxFont' => 10, 'minFont' => 20]);
    }

    /**
     * @test
     * @uses \MeCms\View\Cell\PostsTagsWidgetsCell::popular()
     */
    public function testPopular(): void
    {
        $widget = 'MeCms.PostsTags::popular';

        //Tries using the style (`maxFont` and `minFont`)
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
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
        $result = $this->Widget->widget($widget, $this->example)->render();
        $this->assertHtml($expected, $result);

        //Tries with a custom prefix
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
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
        $result = $this->Widget->widget($widget, ['prefix' => '-', 'style' => []] + $this->example)->render();
        $this->assertHtml($expected, $result);

        //Tries to render as form
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/posts/tag/tag'],
            ['div' => ['class' => 'input mb-3 select']],
            'select' => ['name' => 'q', 'class' => 'form-control form-select', 'onchange' => 'sendForm(this)'],
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
        $result = $this->Widget->widget($widget, ['render' => 'form', 'style' => []] + $this->example)->render();
        $this->assertHtml($expected, $result);

        //Tries to render as list
        $expected = [
            ['div' => ['class' => 'widget mb-5']],
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
        $result = $this->Widget->widget($widget, ['render' => 'list', 'style' => []] + $this->example)->render();
        $this->assertHtml($expected, $result);

        $expected = [
            ['div' => ['class' => 'widget mb-5']],
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
        $result = $this->Widget->widget($widget, ['shuffle' => true, 'style' => []] + $this->example)->render();
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
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'form'] + $this->example)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'] + $this->example)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['shuffle' => true] + $this->example)->render());

        /**
         * With tags that have the same `post_count` value
         */
        //Adds some tag, with the same `post_count`
        foreach (['example1', 'example2'] as $tag) {
            $entity = $this->Table->newEntity(compact('tag') + ['post_count' => 999], ['accessibleFields' => ['post_count' => true]]);
            $this->assertNotFalse($this->Table->save($entity));
        }

        $expected = [
            ['div' => ['class' => 'widget mb-5']],
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
        $result = $this->Widget->widget($widget, $this->example)->render();
        $this->assertHtml($expected, $result);

        //Tests cache
        $fromCache = Cache::read('widget_tags_popular_2_max_40_min_12', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals(['example1' => 40, 'example2' => 40], $fromCache->extract('size')->toArray());
    }
}
