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
use MeCms\View\Cell\PostsTagsCell;
use MeCms\View\View\AppView as View;
use Reflection\ReflectionTrait;

/**
 * PostsTagsCellTest class
 */
class PostsTagsCellTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\View\Cell\PostsTagsCell
     */
    protected $PostsTagsCell;

    /**
     * @var \MeCms\Model\Table\TagsTable
     */
    protected $Tags;

    /**
     * @var \MeCms\View\View\AppView
     */
    protected $View;

    /**
     * Options
     * @var array
     */
    protected $options;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.tags',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        Cache::disable();

        $this->PostsTagsCell = new PostsTagsCell();
        $this->Tags = TableRegistry::get('Tags');
        $this->View = new View;

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

        unset($this->PostsTagsCell, $this->Tags, $this->View);
    }

    /**
     * Test for `_getFontSizes()` method
     * @test
     */
    public function testGetFontSizes()
    {
        $result = $this->invokeMethod($this->PostsTagsCell, '_getFontSizes', [[]]);
        $this->assertEquals([40, 12], $result);

        $result = $this->invokeMethod($this->PostsTagsCell, '_getFontSizes', [['maxFont' => 20]]);
        $this->assertEquals([20, 12], $result);

        $result = $this->invokeMethod($this->PostsTagsCell, '_getFontSizes', [['minFont' => 20]]);
        $this->assertEquals([40, 20], $result);

        $result = $this->invokeMethod($this->PostsTagsCell, '_getFontSizes', [['maxFont' => 30, 'minFont' => 20]]);
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
        $this->invokeMethod($this->PostsTagsCell, '_getFontSizes', [['maxFont' => 10, 'minFont' => 20]]);
    }

    /**
     * Test for `popular()` method
     * @test
     */
    public function testPopular()
    {
        //Tries using the style (`maxFont` and `minFont`)
        $result = $this->View->cell(MECMS . '.PostsTags::popular', $this->options)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['div' => true],
            ['a' => ['href' => '/posts/tag/cat', 'style' => 'font-size:40px;', 'title' => 'Cat']],
            '#Cat',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => '/posts/tag/dog', 'style' => 'font-size:12px;', 'title' => 'Dog']],
            '#Dog',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries with a custom prefix
        $result = $this->View->cell(MECMS . '.PostsTags::popular', am($this->options, [
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
            ['a' => ['href' => '/posts/tag/cat', 'title' => 'Cat']],
            '-Cat',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => '/posts/tag/dog', 'title' => 'Dog']],
            '-Dog',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries to render as form
        $result = $this->View->cell(MECMS . '.PostsTags::popular', am($this->options, [
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
            'Cat (3)',
            '/option',
            ['option' => ['value' => 'dog']],
            'Dog (2)',
            '/option',
            '/select',
            '/div',
            '/form',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries to render as list
        $result = $this->View->cell(MECMS . '.PostsTags::popular', am($this->options, [
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
            ['a' => ['href' => '/posts/tag/cat', 'title' => 'Cat']],
            'Cat',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ' ',
            ['a' => ['href' => '/posts/tag/dog', 'title' => 'Dog']],
            'Dog',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries with shuffle
        $result = $this->View->cell(MECMS . '.PostsTags::popular', am($this->options, [
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
            ['a' => ['href' => 'preg:/\/posts\/tag\/(cat|dog)/', 'title' => 'preg:/(Cat|Dog)/']],
            'preg:/#(Cat|Dog)/',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => 'preg:/\/posts\/tag\/(cat|dog)/', 'title' => 'preg:/(Cat|Dog)/']],
            'preg:/#(Cat|Dog)/',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on tags index
        $request = new Request(Router::url(['_name' => 'postsTags']));
        $this->View = new View($request);
        $result = $this->View->cell(MECMS . '.PostsTags::popular')->render();
        $this->assertEmpty($result);

        //Deletes all tags
        $this->Tags->deleteAll(['id >=' => 1]);

        //Empty with no tags
        $result = $this->View->cell(MECMS . '.PostsTags::popular')->render();
        $this->assertEmpty($result);
    }

    /**
     * Test for `popular()` method, with tags that have the same `post_count`
     *  value
     * @test
     */
    public function testPopularWithTagsSamePostCount()
    {
        //Adds some tag, with the same `post_count`
        foreach ([
            ['tag' => 'Example1', 'post_count' => 999],
            ['tag' => 'Example2', 'post_count' => 999],
        ] as $data) {
            $entity = $this->Tags->newEntity($data);
            $this->assertNotFalse($this->Tags->save($entity));
        }

        $result = $this->View->cell(MECMS . '.PostsTags::popular', $this->options)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Popular tags',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['div' => true],
            ['a' => ['href' => '/posts/tag/example1', 'style' => 'font-size:40px;', 'title' => 'Example1']],
            '#Example1',
            '/a',
            '/div',
            ['div' => true],
            ['a' => ['href' => '/posts/tag/example2', 'style' => 'font-size:40px;', 'title' => 'Example2']],
            '#Example2',
            '/a',
            '/div',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }
}