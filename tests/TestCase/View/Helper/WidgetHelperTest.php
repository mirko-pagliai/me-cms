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

use Cake\Core\Configure;
use Cake\Core\Plugin;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;
use MeTools\TestSuite\TestCase;

/**
 * WidgetHelperTest class
 */
class WidgetHelperTest extends TestCase
{
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

        Plugin::unload('TestPlugin');
    }

    /**
     * Tests for `_getAll()` method
     * @test
     */
    public function testGetAll()
    {
        $widgets = collection($this->invokeMethod($this->Widget, '_getAll'))
            ->map(function ($widget) {
                return collection(array_keys($widget))->first();
            })->toList();

        $this->assertEquals([
            ME_CMS . '.Pages::categories',
            ME_CMS . '.Pages::pages',
            ME_CMS . '.Photos::albums',
            ME_CMS . '.Photos::latest',
            ME_CMS . '.Photos::random',
            ME_CMS . '.Posts::categories',
            ME_CMS . '.Posts::latest',
            ME_CMS . '.Posts::months',
            ME_CMS . '.Posts::search',
            ME_CMS . '.PostsTags::popular',
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

        $widgets = $this->invokeMethod($this->Widget, '_getAll');
        $this->assertEquals([
            ['First' => []],
            ['Second' => []],
            ['Third' => ['key' => 'value']],
            ['Third' => ['anotherKey' => 'anotherValue']],
            ['Fourth' => ['fourth' => 'fourthValue']],
            ['Fifth' => []]
        ], $widgets);

        //Test empty values from widgets
        foreach ([[], null, false] as $value) {
            Configure::write('Widgets.general', $value);
            $result = $this->invokeMethod($this->Widget, '_getAll');
            $this->assertEquals([], $result);
        }

        //Sets some widgets for the homepage
        Configure::write('Widgets.homepage', ['ExampleForHomepage']);

        $widgets = $this->invokeMethod($this->Widget, '_getAll');
        $this->assertEquals([['ExampleForHomepage' => []]], $widgets);

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

        $result = $this->Widget->all();
        $this->assertEquals('An example widget' . PHP_EOL . 'An example widget from a plugin', $result);

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
        $this->assertInstanceOf('App\View\Cell\ExampleWidgetsCell', $cell);

        $cell = $this->Widget->widget('Example', ['example of value']);
        $this->assertEquals('display', $cell->action);
        $this->assertEquals([0 => 'example of value'], $cell->args);
        $this->assertEquals('display', $cell->template);
        $this->assertInstanceOf('App\View\Cell\ExampleWidgetsCell', $cell);

        //From plugin
        $cell = $this->Widget->widget('TestPlugin.PluginExample');
        $this->assertEquals('display', $cell->action);
        $this->assertEquals([], $cell->args);
        $this->assertEquals('display', $cell->template);
        $this->assertInstanceOf('TestPlugin\View\Cell\PluginExampleWidgetsCell', $cell);
    }
}
