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
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;

/**
 * PhotosWidgetsCellTest class
 */
class PhotosWidgetsCellTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PhotosTable
     */
    protected $Photos;

    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected $Widget;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.photos',
        'plugin.me_cms.photos_albums',
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

        $this->Photos = TableRegistry::get(ME_CMS . '.Photos');

        $this->Widget = new WidgetHelper(new View);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Photos, $this->Widget);
    }

    /**
     * Test for `albums()` method
     * @test
     */
    public function testAlbums()
    {
        $widget = ME_CMS . '.Photos::albums';

        $result = $this->Widget->widget($widget)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
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
        $this->assertHtml($expected, $result);

        //Renders as list
        $result = $this->Widget->widget($widget, ['render' => 'list'])->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Albums',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'ul' => ['class' => 'fa-ul'],
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
            ' ',
            '/i',
            ['a' => ['href' => '/album/another-album-test', 'title' => 'Another album test']],
            'Another album test',
            '/a',
            '/li',
            ['li' => true],
            ['i' => ['class' => 'fa fa-caret-right fa-li']],
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
        $this->assertHtml($expected, $result);

        //Empty on albums index
        $widget = $this->Widget->widget($widget);
        $widget->request->env('REQUEST_URI', Router::url(['_name' => 'albums']));
        $this->assertEmpty($widget->render());

        //Tests cache
        $fromCache = Cache::read('widget_albums', $this->Photos->cache);
        $this->assertEquals(2, $fromCache->count());
        $this->assertEquals([
            'another-album-test',
            'test-album',
        ], array_keys($fromCache->toArray()));
    }

    /**
     * Test for `albums()` method, with no photos
     * @test
     */
    public function testAlbumsNoPhotos()
    {
        $widget = ME_CMS . '.Photos::albums';

        $this->Photos->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget($widget)->render());
        $this->assertEmpty($this->Widget->widget($widget, ['render' => 'list'])->render());
    }

    /**
     * Test for `latest()` method
     * @test
     */
    public function testLatest()
    {
        $widget = ME_CMS . '.Photos::latest';

        $result = $this->Widget->widget($widget)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Latest photo',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => ''],
            'img' => ['src', 'alt', 'class' => 'img-responsive'],
            '/a',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries another limit
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Latest 2 photos',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-responsive']],
            '/a',
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-responsive']],
            '/a',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on same controllers
        foreach (['Photos', 'PhotosAlbums'] as $controller) {
            $widgetClass = $this->Widget->widget($widget);
            $widgetClass->request = $widgetClass->request->withParam('controller', $controller);
            $this->assertEmpty($widgetClass->render());
        }

        //Tests cache
        $fromCache = Cache::read('widget_latest_1', $this->Photos->cache);
        $this->assertEquals(1, $fromCache->count());

        $fromCache = Cache::read('widget_latest_2', $this->Photos->cache);
        $this->assertEquals(2, $fromCache->count());
    }

    /**
     * Test for `latest()` method, with no photos
     * @test
     */
    public function testLatestNoPhotos()
    {
        $this->Photos->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget(ME_CMS . '.Photos::latest')->render());
    }

    /**
     * Test for `random()` method
     * @test
     */
    public function testRandom()
    {
        $widget = ME_CMS . '.Photos::random';

        $result = $this->Widget->widget($widget)->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Random photo',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-responsive']],
            '/a',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Tries another limit
        $result = $this->Widget->widget($widget, ['limit' => 2])->render();

        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Random 2 photos',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-responsive']],
            '/a',
            ['a' => ['href' => '/albums', 'class' => 'thumbnail', 'title' => '']],
            ['img' => ['src', 'alt', 'class' => 'img-responsive']],
            '/a',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        //Empty on same controllers
        foreach (['Photos', 'PhotosAlbums'] as $controller) {
            $widgetClass = $this->Widget->widget($widget);
            $widgetClass->request = $widgetClass->request->withParam('controller', $controller);
            $this->assertEmpty($widgetClass->render());
        }

        //Tests cache
        $fromCache = Cache::read('widget_random_1', $this->Photos->cache);
        $this->assertEquals(3, $fromCache->count());

        $fromCache = Cache::read('widget_random_2', $this->Photos->cache);
        $this->assertEquals(3, $fromCache->count());
    }

    /**
     * Test for `random()` method, with no photos
     * @test
     */
    public function testRandomNoPhotos()
    {
        $this->Photos->deleteAll(['id >=' => 1]);

        $this->assertEmpty($this->Widget->widget(ME_CMS . '.Photos::random')->render());
    }
}
