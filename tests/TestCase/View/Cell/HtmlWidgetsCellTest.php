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
