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
    public function testDisplay(): void
    {
        $widget = 'MeCms.Html';

        $expected = 'A custom widget';
        $this->assertEquals($expected, $this->Widget->widget($widget, ['template' => 'custom_html'])->render());

        $expected = [
            ['div' => ['class' => 'widget mb-4']],
            ['div' => ['class' => 'widget-content']],
            'A custom widget',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $this->Widget->widget($widget, ['template' => 'custom_html2'])->render());

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
        $this->assertHtml($expected, $this->Widget->widget($widget, ['template' => 'custom_html3'])->render());
    }
}
