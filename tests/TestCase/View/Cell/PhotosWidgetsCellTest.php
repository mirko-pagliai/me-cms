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
use MeCms\Model\Table\PhotosTable;
use MeCms\TestSuite\CellTestCase;

/**
 * PhotosWidgetsCellTest class
 */
class PhotosWidgetsCellTest extends CellTestCase
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
        'plugin.MeCms.Photos',
        'plugin.MeCms.PhotosAlbums',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Table = $this->getMockForModel('Photos', null, ['className' => PhotosTable::class]);
    }

    /**
     * Test for `albums()` method
     * @test
     */
    public function testAlbums()
    {
        $widget = 'MeCms.Photos::albums';

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Albums',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'form' => ['method' => 'get', 'accept-charset' => 'utf-8', 'action' => '/album/album'],
            ['div' => ['class' => 'form-group input select']],
            'select' => ['name' => 'q', 'onchange' => 'send_form(this)', 'class' => 'form-control'],
            ['option' => ['value' => '']],
            '/option',
            ['option' => ['value' => 'another-album-test']],
            'Another album test (2)',
            '/option',
            ['option' => ['value' => 'test-album']],
            'Test album (2)',
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
            'Albums',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/album/another-album-test', 'title' => 'Another album test']],
            'Another album test',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fas fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/album/test-album', 'title' => 'Test album']],
            'Test album',
            '/a',
            '/li',
            '/ul',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();
        $this->assertHtml($expected, $result);

        //Empty on albums index
        $request = $this->Widget->getView()->getRequest()->withEnv('REQUEST_URI', Router::url(['_name' => 'albums']));
        $this->Widget->getView()->setRequest($request);
        $this->assertEmpty($this->Widget->widget($widget)->render());

        //Tests cache
        $fromCache = Cache::read('widget_albums', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());
        $this->assertArrayKeysEqual(['another-album-test', 'test-album'], $fromCache->toArray());

        //With no photos
        Cache::clearAll();
        $this->Table->deleteAll(['id >=' => 1]);
        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `latest()` method
     * @test
     */
    public function testLatest()
    {
        $widget = 'MeCms.Photos::latest';

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Latest photo',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'a' => ['href' => '/albums'],
            'img' => ['src', 'alt', 'class' => 'img-fluid thumbnail'],
            '/a',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Tries another limit
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Latest 2 photos',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['a' => ['href' => '/albums']],
            ['img' => ['src', 'alt', 'class' => 'img-fluid thumbnail']],
            '/a',
            ['a' => ['href' => '/albums']],
            ['img' => ['src', 'alt', 'class' => 'img-fluid thumbnail']],
            '/a',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();
        $this->assertHtml($expected, $result);

        //Empty on same controllers
        foreach (['Photos', 'PhotosAlbums'] as $controller) {
            $request = $this->Widget->getView()->getRequest()->withParam('controller', $controller);
            $this->Widget->getView()->setRequest($request);
            $this->assertEmpty($this->Widget->widget($widget)->render());
        }

        //Tests cache
        $fromCache = Cache::read('widget_latest_1', $this->Table->getCacheName());
        $this->assertEquals(1, $fromCache->count());

        $fromCache = Cache::read('widget_latest_2', $this->Table->getCacheName());
        $this->assertEquals(2, $fromCache->count());

        //With no photos
        Cache::clearAll();
        $this->Table->deleteAll(['id >=' => 1]);
        $this->assertEmpty($this->Widget->widget($widget)->render());
    }

    /**
     * Test for `random()` method
     * @test
     */
    public function testRandom()
    {
        $widget = 'MeCms.Photos::random';

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Random photo',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-fluid']],
            '/a',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget)->render();
        $this->assertHtml($expected, $result);

        //Tries another limit
        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Random 2 photos',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-fluid']],
            '/a',
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-fluid']],
            '/a',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();
        $this->assertHtml($expected, $result);

        //Empty on same controllers
        foreach (['Photos', 'PhotosAlbums'] as $controller) {
            $request = $this->Widget->getView()->getRequest()->withParam('controller', $controller);
            $this->Widget->getView()->setRequest($request);
            $this->assertEmpty($this->Widget->widget($widget)->render());
        }

        //Tests cache
        $fromCache = Cache::read('widget_random_1', $this->Table->getCacheName());
        $this->assertEquals(3, $fromCache->count());

        $fromCache = Cache::read('widget_random_2', $this->Table->getCacheName());
        $this->assertEquals(3, $fromCache->count());

        //With no photos
        Cache::clearAll();
        $this->Table->deleteAll(['id >=' => 1]);
        $this->assertEmpty($this->Widget->widget($widget)->render());
    }
}
