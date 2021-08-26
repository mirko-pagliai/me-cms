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

namespace MeCms\Test\TestCase\View\Helper;

use App\View\Cell\ExampleWidgetsCell;
use Cake\Core\Configure;
use MeTools\TestSuite\HelperTestCase;
use TestPlugin\View\Cell\PluginExampleWidgetsCell;

/**
 * WidgetHelperTest class
 * @property \MeCms\View\Helper\WidgetHelper $Helper
 */
class WidgetHelperTest extends HelperTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadPlugins(['TestPlugin']);

        $request = $this->Helper->getView()->getRequest()->withEnv('REQUEST_URI', '/');
        $this->Helper->getView()->setRequest($request);
    }

    /**
     * Tests for `getAll()` method
     * @test
     */
    public function testGetAll(): void
    {
        $getAllMethod = function () {
            return $this->invokeMethod($this->Helper, 'getAll');
        };

        $widgets = array_map('array_value_first', array_map('array_keys', $getAllMethod()));
        $this->assertSame([
            'MeCms.Pages::categories',
            'MeCms.Pages::pages',
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
        $this->assertSame([
            ['First' => []],
            ['Second' => []],
            ['Third' => ['key' => 'value']],
            ['Third' => ['anotherKey' => 'anotherValue']],
            ['Fourth' => ['fourth' => 'fourthValue']],
            ['Fifth' => []],
        ], $getAllMethod());

        //Test empty values from widgets
        foreach ([[], null, false] as $value) {
            Configure::write('Widgets.general', $value);
            $this->assertSame([], $getAllMethod());
        }

        //Sets some widgets for the homepage
        Configure::write('Widgets.homepage', ['ExampleForHomepage']);
        $this->assertSame([['ExampleForHomepage' => []]], $getAllMethod());

        //Resets
        Configure::write('Widgets.homepage', []);
    }

    /**
     * Tests for `all()` method
     * @test
     */
    public function testAll(): void
    {
        //Sets some widgets
        Configure::write('Widgets.general', ['Example', 'TestPlugin.PluginExample']);
        $this->assertSame('An example widget' . PHP_EOL . 'An example widget from a plugin', $this->Helper->all());

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
    public function testWidget(): void
    {
        $cell = $this->Helper->widget('Example');
        $this->assertSame('display', $cell->__debugInfo()['action']);
        $this->assertSame([], $cell->__debugInfo()['args']);
        $this->assertNull($cell->viewBuilder()->getPlugin());
        $this->assertSame('display', $cell->viewBuilder()->getTemplate());
        $this->assertInstanceOf(ExampleWidgetsCell::class, $cell);
        $this->assertSame('An example widget', $cell->render());

        $cell = $this->Helper->widget('Example', ['example of value']);
        $this->assertSame('display', $cell->__debugInfo()['action']);
        $this->assertSame([0 => 'example of value'], $cell->__debugInfo()['args']);
        $this->assertNull($cell->viewBuilder()->getPlugin());
        $this->assertSame('display', $cell->viewBuilder()->getTemplate());
        $this->assertSame('An example widget', $cell->render());

        //From plugin
        $cell = $this->Helper->widget('TestPlugin.PluginExample');
        $this->assertSame('display', $cell->__debugInfo()['action']);
        $this->assertSame([], $cell->__debugInfo()['args']);
        $this->assertSame('TestPlugin', $cell->viewBuilder()->getPlugin());
        $this->assertSame('display', $cell->viewBuilder()->getTemplate());
        $this->assertInstanceOf(PluginExampleWidgetsCell::class, $cell);
        $this->assertSame('An example widget from a plugin', $cell->render());
    }
}
