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

use MeCms\TestSuite\CellTestCase;

/**
 * HtmlWidgetsCellTest class
 */
class HtmlWidgetsCellTest extends CellTestCase
{
    /**
     * Test for `display()` method
     * @test
     */
    public function testDisplay()
    {
        $widget = ME_CMS . '.Html';

        $result = $this->Widget->widget($widget, ['template' => 'custom_html'])->render();
        $this->assertEquals('A custom widget', $result);

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            ['div' => ['class' => 'widget-content']],
            'A custom widget',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['template' => 'custom_html2'])->render();
        $this->assertHtml($expected, $result);

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            'h4' => ['class' => 'widget-title'],
            'Custom title',
            '/h4',
            ['div' => ['class' => 'widget-content']],
            'A custom widget',
            '/div',
            '/div',
        ];
        $result = $this->Widget->widget($widget, ['template' => 'custom_html3'])->render();
        $this->assertHtml($expected, $result);
    }
}
