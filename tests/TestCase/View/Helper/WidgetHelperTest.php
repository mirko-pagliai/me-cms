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
namespace MeCms\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use MeCms\View\View\AppView as View;
use MeCms\View\Helper\WidgetHelper;
use Reflection\ReflectionTrait;

/**
 * WidgetHelperTest class
 */
class WidgetHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\View\Helper\WidgetHelper
     */
    protected $Widget;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Widget = new WidgetHelper(new View);

        Plugin::load('TestPlugin');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Widget);

        Plugin::unload('TestPlugin');
    }

    /**
     * Tests for `_getAll()` method
     * @test
     */
    public function testGetAll()
    {
        $result = $this->invokeMethod($this->Widget, '_getAll');
        $this->assertEquals([
            'MeCms.Pages::categories',
            'MeCms.Pages::pages',
            'MeCms.Photos::albums',
            'MeCms.Photos::latest',
            'MeCms.Photos::random',
            'MeCms.Posts::categories',
            'MeCms.Posts::latest',
            'MeCms.Posts::months',
            'MeCms.Posts::search',
            'MeCms.PostsTags::popular',
        ], array_keys($result));

        //Sets some widgets
        Configure::write('Widgets.general', [
            'First' => [],
            'Second',
            'Third' => [],
            'Fourth',
        ]);

        $result = $this->invokeMethod($this->Widget, '_getAll');
        $this->assertEquals([
            'First',
            'Second',
            'Third',
            'Fourth',
        ], array_keys($result));

        //Test empty values from widgets
        foreach ([[], null, false] as $value) {
            Configure::write('Widgets.general', $value);
            $result = $this->invokeMethod($this->Widget, '_getAll');
            $this->assertEquals([], $result);
        }
    }

    /**
     * Tests for `all()` method
     * @test
     */
    public function testAll()
    {
        //Sets some widgets
        Configure::write('Widgets.general', ['Example', 'TestPlugin.PluginExample']);

        $result = $this->Widget->all();
        $this->assertEquals('An example widget' . PHP_EOL . PHP_EOL . 'An example widget from a plugin', $result);

        //Test empty values from widgets
        foreach ([[], null, false] as $value) {
            Configure::write('Widgets.general', $value);
            $result = $this->Widget->all();
            $this->assertEquals(null, $result);
        }
    }

    /**
     * Tests for `widget()` method
     * @test
     */
    public function testWidget()
    {
        $cell = $this->Widget->widget('Example');
        $this->assertEquals('display', $cell->action);
        $this->assertEquals([], $cell->args);
        $this->assertEquals('display', $cell->template);
        $this->assertEquals('App\View\Cell\ExampleCell', get_class($cell));

        $cell = $this->Widget->widget('Example', ['example of value']);
        $this->assertEquals('display', $cell->action);
        $this->assertEquals([0 => 'example of value'], $cell->args);
        $this->assertEquals('display', $cell->template);
        $this->assertEquals('App\View\Cell\ExampleCell', get_class($cell));

        //From plugin
        $cell = $this->Widget->widget('TestPlugin.PluginExample');
        $this->assertEquals('display', $cell->action);
        $this->assertEquals([], $cell->args);
        $this->assertEquals('display', $cell->template);
        $this->assertEquals('TestPlugin\View\Cell\PluginExampleCell', get_class($cell));
    }
}
