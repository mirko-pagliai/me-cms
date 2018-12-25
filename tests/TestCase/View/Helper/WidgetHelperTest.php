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
namespace MeCms\Test\TestCase\View\Helper;

use App\View\Cell\ExampleWidgetsCell;
use Cake\Core\Configure;
use MeTools\TestSuite\HelperTestCase;
use TestPlugin\View\Cell\PluginExampleWidgetsCell;

/**
 * WidgetHelperTest class
 */
class WidgetHelperTest extends HelperTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadPlugins(['TestPlugin']);
    }

    /**
     * Tests for `getAll()` method
     * @test
     */
    public function testGetAll()
    {
        $getAllMethod = function () {
            return $this->invokeMethod($this->Helper, 'getAll');
        };

        $widgets = array_map('first_value', array_map('array_keys', $getAllMethod()));
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
        ], $widgets);

        //Sets some widgets
        Configure::write('Widgets.general', [
            'First' => [],
            'Second',
            ['Third' => ['key' => 'value']],
            ['Third' => ['anotherKey' => 'anotherValue']],
            'Fourth' => ['fourth' => 'fourthValue'],
            ['Fifth'],
        ]);
        $this->assertEquals([
            ['First' => []],
            ['Second' => []],
            ['Third' => ['key' => 'value']],
            ['Third' => ['anotherKey' => 'anotherValue']],
            ['Fourth' => ['fourth' => 'fourthValue']],
            ['Fifth' => []]
        ], $getAllMethod());

        //Test empty values from widgets
        foreach ([[], null, false] as $value) {
            Configure::write('Widgets.general', $value);
            $this->assertEquals([], $getAllMethod());
        }

        //Sets some widgets for the homepage
        Configure::write('Widgets.homepage', ['ExampleForHomepage']);
        $this->assertEquals([['ExampleForHomepage' => []]], $getAllMethod());

        //Resets
        Configure::write('Widgets.homepage', []);
    }

    /**
     * Tests for `all()` method
     * @test
     */
    public function testAll()
    {
        //Sets some widgets
        Configure::write('Widgets.general', ['Example', 'TestPlugin.PluginExample']);
        $this->assertEquals('An example widget' . PHP_EOL . 'An example widget from a plugin', $this->Helper->all());

        //Test empty values from widgets
        foreach ([[], null, false] as $value) {
            Configure::write('Widgets.general', $value);
            $this->assertEquals(null, $this->Helper->all());
        }
    }

    /**
     * Tests for `widget()` method
     * @test
     */
    public function testWidget()
    {
        $cell = $this->Helper->widget('Example');
        list($action, $args) = array_values($cell->__debugInfo());
        $this->assertEquals('display', $action);
        $this->assertEquals([], $args);
        $this->assertEquals('display', $cell->viewBuilder()->getTemplate());
        $this->assertInstanceOf(ExampleWidgetsCell::class, $cell);

        $cell = $this->Helper->widget('Example', ['example of value']);
        list($action, $args) = array_values($cell->__debugInfo());
        $this->assertEquals('display', $action);
        $this->assertEquals([0 => 'example of value'], $args);
        $this->assertEquals('display', $cell->viewBuilder()->getTemplate());

        //From plugin
        $cell = $this->Helper->widget('TestPlugin.PluginExample');
        list($action, $args) = array_values($cell->__debugInfo());
        $this->assertEquals('display', $action);
        $this->assertEquals([], $args);
        $this->assertEquals('display', $cell->viewBuilder()->getTemplate());
        $this->assertInstanceOf(PluginExampleWidgetsCell::class, $cell);
    }
}
