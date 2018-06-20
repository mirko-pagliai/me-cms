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
use Cake\View\View;
use MeCms\View\Helper\MenuBuilderHelper;
use MeTools\TestSuite\TestCase;
use Tools\ReflectionTrait;

/**
 * MenuBuilderHelperTest class
 */
class MenuBuilderHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\View\Helper\MenuBuilderHelper
     */
    protected $MenuBuilder;

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->MenuBuilder = new MenuBuilderHelper(new View);

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

        Plugin::unload('TestPlugin');
        Plugin::unload('TestPluginTwo');
    }

    /**
     * Tests for `getMenuMethods()` method
     * @test
     */
    public function testGetMenuMethods()
    {
        $getMenuMethodsMethod = function ($plugin) {
            return $this->invokeMethod($this->MenuBuilder, 'getMenuMethods', [$plugin]);
        };

        $result = $getMenuMethodsMethod(ME_CMS);
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
        foreach (['_invalidMethod', '__otherInvalidMethod', 'articles', 'other_items'] as $method) {
            $this->assertTrue(method_exists('\TestPlugin\View\Helper\MenuHelper', $method));
        }

        $result = $getMenuMethodsMethod('TestPlugin');
        $expected = ['articles', 'other_items'];
        $this->assertEquals($expected, $result);

        //This plugin has no menu methods
        $result = $getMenuMethodsMethod('TestPluginTwo');
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
        $this->assertArrayKeysEqual([ME_CMS . '.posts', ME_CMS . '.pages', ME_CMS . '.photos'], $result);

        //Foreach menu
        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertArrayKeysEqual(['links', 'title', 'titleOptions'], $menu);

            //Checks links
            foreach ($menu['links'] as $link) {
                $this->assertIsString($link[0]);
                $this->assertNotEmpty($link[1]);
            }
        }

        $result = $this->MenuBuilder->generate('TestPlugin');

        //Checks array keys (menu names)
        $this->assertArrayKeysEqual(['TestPlugin.articles', 'TestPlugin.other_items'], $result);

        //Foreach menu
        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertArrayKeysEqual(['links', 'title', 'titleOptions'], $menu);

            //Checks links
            foreach ($menu['links'] as $link) {
                $this->assertIsString($link[0]);
                $this->assertNotEmpty($link[1]);
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
            ['div' => ['class' => 'card']],
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
            ['div' => ['class' => 'card']],
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
        $this->assertArrayKeysEqual(['TestPlugin.articles', 'TestPlugin.other_items'], $result);

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
            '/a',
            ['div' => ['class' => 'dropdown-menu']],
            ['a' => ['href' => '/', 'class' => 'dropdown-item', 'title' => 'First link']],
            'First link',
            '/a',
            ['a' => ['href' => '/', 'class' => 'dropdown-item', 'title' => 'Second link']],
            'Second link',
            '/a',
            '/div',
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
            '/a',
            ['div' => ['class' => 'dropdown-menu']],
            ['a' => ['href' => '/', 'class' => 'dropdown-item', 'title' => 'Third link']],
            'Third link',
            '/a',
            ['a' => ['href' => '/', 'class' => 'dropdown-item', 'title' => 'Fourth link']],
            'Fourth link',
            '/a',
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }
}
