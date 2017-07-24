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

        Plugin::load('TestPlugin');
        Plugin::load('TestPluginTwo');
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->MenuBuilder, $this->Html, $this->View);

        Plugin::unload('TestPlugin');
        Plugin::unload('TestPluginTwo');
    }

    /**
     * Tests for `getMenuMethods()` method
     * @test
     */
    public function testGetMenuMethods()
    {
        $result = $this->MenuBuilder->getMenuMethods(ME_CMS);
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
        $this->assertTrue(method_exists('\TestPlugin\View\Helper\MenuHelper', '_invalidMethod'));
        $this->assertTrue(method_exists('\TestPlugin\View\Helper\MenuHelper', '__otherInvalidMethod'));
        $this->assertTrue(method_exists('\TestPlugin\View\Helper\MenuHelper', 'articles'));
        $this->assertTrue(method_exists('\TestPlugin\View\Helper\MenuHelper', 'other_items'));

        $result = $this->MenuBuilder->getMenuMethods('TestPlugin');
        $expected = ['articles', 'other_items'];
        $this->assertEquals($expected, $result);

        //This plugin has no menu methods
        $result = $this->MenuBuilder->getMenuMethods('TestPluginTwo');
        $this->assertEmpty($result);
    }

    /**
     * Tests for `generate()` method
     * @test
     */
    public function testGenerate()
    {
        $result = $this->MenuBuilder->generate(ME_CMS);

        //Checks array keys (menu names)
        $this->assertEquals([ME_CMS . '.posts', ME_CMS . '.pages', ME_CMS . '.photos'], array_keys($result));

        //Foreach menu
        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertEquals(['menu', 'title', 'titleOptions'], array_keys($menu));

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
        $this->assertEquals(['TestPlugin.articles', 'TestPlugin.other_items'], array_keys($result));

        //Foreach menu
        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertEquals(['menu', 'title', 'titleOptions'], array_keys($menu));

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

        $this->assertEmpty($this->MenuBuilder->generate('TestPluginTwo'));
    }

    /**
     * Tests for `renderAsCollapse()` method
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
     * Tests for `renderAsDropdown()` method
     * @test
     */
    public function testRenderAsDropdown()
    {
        $result = $this->MenuBuilder->renderAsDropdown('TestPlugin');

        //Checks array keys (menu names)
        $this->assertEquals(['TestPlugin.articles', 'TestPlugin.other_items'], array_keys($result));

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
}
