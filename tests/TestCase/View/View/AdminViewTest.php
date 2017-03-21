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
namespace MeCms\Test\TestCase\View\View;

use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use MeCms\View\View\AdminView as View;
use Reflection\ReflectionTrait;

/**
 * AdminViewTest class
 */
class AdminViewTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->View = new View(new Request);
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
     * Tests for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $this->assertEquals('MeCms.admin', $this->View->layout());
    }

    /**
     * Tests for `initialize()` method
     * @test
     */
    public function testInitialize()
    {
        //Gets loaded helpers, as class names
        $helpers = collection($this->View->helpers()->loaded())->map(function ($helper) {
            return get_class($this->View->helpers()->get($helper));
        })->toArray();

        $this->assertEquals([
            'MeTools\View\Helper\HtmlHelper',
            'MeTools\View\Helper\DropdownHelper',
            'MeTools\View\Helper\FormHelper',
            'MeTools\View\Helper\LibraryHelper',
            'MeTools\View\Helper\PaginatorHelper',
            'Assets\View\Helper\AssetHelper',
            'Thumber\View\Helper\ThumbHelper',
            'WyriHaximus\MinifyHtml\View\Helper\MinifyHtmlHelper',
            'MeCms\View\Helper\MenuBuilderHelper',
            'Gourmet\CommonMark\View\Helper\CommonMarkHelper',
        ], $helpers);
    }

    /**
     * Tests for `render()` method
     * @test
     */
    public function testRender()
    {
        $this->View->render(false);

        $priorities = $this->View->get('priorities');
        $this->assertEquals([
            1 => '1 - Very low',
            2 => '2 - Low',
            3 => '3 - Normal',
            4 => '4 - High',
            5 => '5 - Very high',
        ], $priorities);
    }
}
