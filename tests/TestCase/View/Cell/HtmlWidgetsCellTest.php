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

use Cake\TestSuite\TestCase;
use MeCms\View\Helper\WidgetHelper;
use MeCms\View\View\AppView as View;

/**
 * HtmlWidgetsCellTest class
 */
class HtmlWidgetsCellTest extends TestCase
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
        $this->Widget = new WidgetHelper(new View);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Widget);
    }

    /**
     * Test for `display()` method
     * @test
     */
    public function testDisplay()
    {
        $widget = ME_CMS . '.Html';

        $result = $this->Widget->widget($widget, ['template' => 'custom_html'])->render();
        $this->assertEquals('A custom widget', $result);

        $result = $this->Widget->widget($widget, ['template' => 'custom_html2'])->render();
        $expected = [
            ['div' => ['class' => 'widget']],
            ['div' => ['class' => 'widget-content']],
            'A custom widget',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Widget->widget($widget, ['template' => 'custom_html3'])->render();
        $expected = [
            ['div' => ['class' => 'widget']],
            'h4' => ['class' => 'widget-title'],
            'Custom title',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'A custom widget',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }
}
