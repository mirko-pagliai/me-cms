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

use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use MeCms\View\Helper\MenuBuilderHelper;

/**
 * MenuBuilderHelperTest class
 */
class MenuBuilderHelperTest extends TestCase
{
    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->View = new View();
        $this->MenuBuilder = new MenuBuilderHelper($this->View);

        Plugin::load(
            'TestPlugin',
            ['path' => 'tests/test_app/Plugin/TestPlugin/src']
        );

        Plugin::load(
            'TestPluginTwo',
            ['path' => 'tests/test_app/Plugin/TestPluginTwo/src']
        );
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->MenuBuilder, $this->Html, $this->View);
    }

    /**
     * Tests for `getMenuMethods()` method
     * @return void
     * @test
     */
    public function testGetMenuMethods()
    {
        $result = $this->MenuBuilder->getMenuMethods(MECMS);
        $expected = [
            'posts',
            'pages',
            'photos',
            'banners',
            'users',
            'backups',
            'systems',
        ];
        $this->assertEquals($expected, $result);

        //Checks that methods exist
        $this->assertTrue(method_exists(
            '\TestPlugin\View\Helper\MenuHelper',
            '_invalidMethod'
        ));
        $this->assertTrue(method_exists(
            '\TestPlugin\View\Helper\MenuHelper',
            '__otherInvalidMethod'
        ));
        $this->assertTrue(method_exists(
            '\TestPlugin\View\Helper\MenuHelper',
            'articles'
        ));
        $this->assertTrue(method_exists(
            '\TestPlugin\View\Helper\MenuHelper',
            'other_items'
        ));

        $result = $this->MenuBuilder->getMenuMethods('TestPlugin');
        $expected = ['articles', 'other_items'];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests for `getMenuMethods()` method whit plugin that does not have the
     *  class `MenuHelper`
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @return void
     * @test
     */
    public function testGetMenuMethodsClassNotExisting()
    {
        $this->MenuBuilder->getMenuMethods('TestPluginTwo');
    }

    /**
     * Tests for `generate()` method
     * @return void
     * @test
     */
    public function testGenerate()
    {
        $result = $this->MenuBuilder->generate(MECMS);

        //Checks array keys (menu names)
        $this->assertEquals(
            ['MeCms.posts', 'MeCms.pages', 'MeCms.photos'],
            array_keys($result)
        );

        //Foreach menu
        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertEquals(
                ['menu', 'title', 'titleOptions'],
                array_keys($menu)
            );

            //Checks each link
            foreach ($menu['menu'] as $link) {
                $expected = [
                    'a' => ['href', 'title'],
                    'preg:/[A-z\s]+/',
                    '/a',
                ];
                $this->assertHtml($expected, $link);
            }
        }

        $result = $this->MenuBuilder->generate('TestPlugin');

        //Checks array keys (menu names)
        $this->assertEquals(
            ['TestPlugin.articles', 'TestPlugin.other_items'],
            array_keys($result)
        );

        //Foreach menu
        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertEquals(
                ['menu', 'title', 'titleOptions'],
                array_keys($menu)
            );

            //Checks each link
            foreach ($menu['menu'] as $link) {
                $expected = [
                    'a' => ['href', 'title'],
                    'preg:/[A-z\s]+/',
                    '/a',
                ];
                $this->assertHtml($expected, $link);
            }
        }
    }

    /**
     * Tests for `generate()` method whit plugin that does not have the class
     *  `MenuHelper`
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @return void
     * @test
     */
    public function testGenerateClassNotExisting()
    {
        $this->MenuBuilder->generate('TestPluginTwo');
    }

    /**
     * Tests for `renderAsCollapse()` method
     * @return void
     * @test
     */
    public function testRenderAsCollapse()
    {
        $result = $this->MenuBuilder->renderAsCollapse('TestPlugin');
        $expected = [
            ['div' => ['class' => 'panel']],
            ['a' => [
                'href' => '#collapse-first-menu',
                'aria-controls' => 'collapse-first-menu',
                'aria-expanded' => 'false',
                'class' => 'collapsed',
                'data-toggle' => 'collapse',
                'title' => 'First menu',
            ]],
            ['i' => ['class' => 'fa fa-home']],
            ' ',
            '/i',
            ' ',
            'First menu',
            '/a',
            ['div' => ['id' => 'collapse-first-menu', 'class' => 'collapse']],
            ['a' => ['href' => '/', 'title' => 'First link']],
            'First link',
            '/a',
            ['a' => ['href' => '/', 'title' => 'Second link']],
            'Second link',
            '/a',
            '/div',
            '/div',
            ['div' => ['class' => 'panel']],
            ['a' => [
                'href' => '#collapse-second-menu',
                'aria-controls' => 'collapse-second-menu',
                'aria-expanded' => 'false',
                'class' => 'collapsed',
                'data-toggle' => 'collapse',
                'title' => 'Second menu',
            ]],
            ['i' => ['class' => 'fa fa-flag']],
            ' ',
            '/i',
            ' ',
            'Second menu',
            '/a',
            ['div' => ['id' => 'collapse-second-menu', 'class' => 'collapse']],
            ['a' => ['href' => '/', 'title' => 'Third link']],
            'Third link',
            '/a',
            ['a' => ['href' => '/', 'title' => 'Fourth link']],
            'Fourth link',
            '/a',
            '/div',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Tests for `renderAsCollapse()` method whit plugin that does not have the
     *  class `MenuHelper`
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @return void
     * @test
     */
    public function testRenderAsCollapseClassNotExisting()
    {
        $this->MenuBuilder->renderAsCollapse('TestPluginTwo');
    }

    /**
     * Tests for `renderAsDropdown()` method
     * @return void
     * @test
     */
    public function testRenderAsDropdown()
    {
        //Checks array keys (menu names)
        $result = $this->MenuBuilder->renderAsDropdown('TestPlugin');
        $this->assertEquals(
            ['TestPlugin.articles', 'TestPlugin.other_items'],
            array_keys($result)
        );

        $result = implode(PHP_EOL, $result);
        $expected = [
            ['a' => [
                'href' => '#',
                'aria-expanded' => 'false',
                'aria-haspopup' => 'true',
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => 'First menu',
            ]],
            ['i' => ['class' => 'fa fa-home']],
            ' ',
            '/i',
            ' ',
            'First menu',
            ' ',
            ['i' => ['class' => 'fa fa-caret-down']],
            ' ',
            '/i',
            '/a',
            ['ul' => ['class' => 'dropdown-menu']],
            ['li' => true],
            ['a' => ['href' => '/', 'title' => 'First link']],
            'First link',
            '/a',
            '/li',
            ['li' => true],
            ['a' => ['href' => '/', 'title' => 'Second link']],
            'Second link',
            '/a',
            '/li',
            '/ul',
            ['a' => [
                'href' => '#',
                'aria-expanded' => 'false',
                'aria-haspopup' => 'true',
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => 'Second menu',
            ]],
            ['i' => ['class' => 'fa fa-flag']],
            ' ',
            '/i',
            ' ',
            'Second menu',
            ' ',
            ['i' => ['class' => 'fa fa-caret-down']],
            ' ',
            '/i',
            '/a',
            ['ul' => ['class' => 'dropdown-menu']],
            ['li' => true],
            ['a' => ['href' => '/', 'title' => 'Third link']],
            'Third link',
            '/a',
            '/li',
            ['li' => true],
            ['a' => ['href' => '/', 'title' => 'Fourth link']],
            'Fourth link',
            '/a',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Tests for `renderAsDropdown()` method whit plugin that does not have the
     *  class `MenuHelper`
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @return void
     * @test
     */
    public function testRenderAsDropdownClassNotExisting()
    {
        $this->MenuBuilder->renderAsDropdown('TestPluginTwo');
    }
}
