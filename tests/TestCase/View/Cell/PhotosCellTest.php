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
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use MeCms\View\View\AppView as View;

/**
 * PhotosCellTest class
 */
class PhotosCellTest extends TestCase
{
    /**
     * @var \MeCms\View\View\AppView
     */
    protected $View;

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
        Cache::disable();

        $this->View = new View;
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->View);
    }

    /**
     * Test for `albums()` method
     * @test
     */
    public function testAlbums()
    {
        $result = $this->View->cell(MECMS . '.Photos::albums')->render();

        //Removes all tabs, including tabs created with multiple spaces
        $result = trim(preg_replace('/\s{2,}/', null, $result));

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
        $result = $this->View->cell(MECMS . '.Photos::albums', ['render' => 'list'])->render();

        //Removes all tabs, including tabs created with multiple spaces
        $result = trim(preg_replace('/\s{2,}/', null, $result));

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
        $request = new Request(Router::url(['_name' => 'albums']));
        $this->View = new View($request);
        $result = $this->View->cell(MECMS . '.Photos::albums')->render();
        $this->assertEmpty($result);
    }

    /**
     * Test for `latest()` method
     * @test
     */
    public function testLatest()
    {
        $result = $this->View->cell(MECMS . '.Photos::latest')->render();

        //Removes all tabs, including tabs created with multiple spaces
        $result = trim(preg_replace('/\s{2,}/', null, $result));

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
        $result = $this->View->cell(MECMS . '.Photos::latest', ['limit' => 2])->render();

        //Removes all tabs, including tabs created with multiple spaces
        $result = trim(preg_replace('/\s{2,}/', null, $result));

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
            $this->View->request->params['controller'] = $controller;
            $result = $this->View->cell(MECMS . '.Photos::latest')->render();
            $this->assertEmpty($result);
        }
    }

    /**
     * Test for `random()` method
     * @test
     */
    public function testRandom()
    {
        $result = $this->View->cell(MECMS . '.Photos::random')->render();

        //Removes all tabs, including tabs created with multiple spaces
        $result = trim(preg_replace('/\s{2,}/', null, $result));

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
        $result = $this->View->cell(MECMS . '.Photos::random', ['limit' => 2])->render();

        //Removes all tabs, including tabs created with multiple spaces
        $result = trim(preg_replace('/\s{2,}/', null, $result));

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
            $this->View->request->params['controller'] = $controller;
            $result = $this->View->cell(MECMS . '.Photos::random')->render();
            $this->assertEmpty($result);
        }
    }
}
