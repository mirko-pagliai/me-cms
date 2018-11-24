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
use MeCms\TestSuite\HelperTestCase;
use TestPlugin\View\Helper\MenuHelper;
use Tools\ReflectionTrait;

/**
 * MenuBuilderHelperTest class
 */
class MenuBuilderHelperTest extends HelperTestCase
{
    use ReflectionTrait;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Plugin::load('TestPlugin');
        Plugin::load('TestPluginTwo');
    }

    /**
     * Called after every test method
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
        $getMenuMethodsMethod = function () {
            return $this->invokeMethod($this->Helper, 'getMenuMethods', func_get_args());
        };

        $expected = [
            'posts',
            'pages',
            'photos',
            'banners',
            'users',
            'backups',
            'systems',
        ];
        $this->assertEquals($expected, $getMenuMethodsMethod('MeCms'));

        //Checks that methods exist
        foreach (['_invalidMethod', '__otherInvalidMethod', 'articles', 'other_items'] as $method) {
            $this->assertTrue(method_exists(MenuHelper::class, $method));
        }

        $expected = ['articles', 'other_items'];
        $this->assertEquals($expected, $getMenuMethodsMethod('TestPlugin'));

        //This plugin has no menu methods
        $this->assertEmpty($getMenuMethodsMethod('TestPluginTwo'));
    }

    /**
     * Tests for `generate()` method
     * @test
     */
    public function testGenerate()
    {
        //Checks array keys (menu names)
        $result = $this->Helper->generate('MeCms');
        $this->assertArrayKeysEqual(['MeCms.posts', 'MeCms.pages', 'MeCms.photos'], $result);

        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertArrayKeysEqual(['links', 'title', 'titleOptions'], $menu);

            //Checks links
            foreach ($menu['links'] as $link) {
                $this->assertIsString($link[0]);
                $this->assertNotEmpty($link[1]);
            }
        }

        //Checks array keys (menu names)
        $result = $this->Helper->generate('TestPlugin');
        $this->assertArrayKeysEqual(['TestPlugin.articles', 'TestPlugin.other_items'], $result);

        foreach ($result as $menu) {
            //Checks array keys (menu values)
            $this->assertArrayKeysEqual(['links', 'title', 'titleOptions'], $menu);

            //Checks links
            foreach ($menu['links'] as $link) {
                $this->assertIsString($link[0]);
                $this->assertNotEmpty($link[1]);
            }
        }

        $this->assertEmpty($this->Helper->generate('TestPluginTwo'));
    }

    /**
     * Tests for `renderAsCollapse()` method
     * @test
     */
    public function testRenderAsCollapse()
    {
        $result = $this->Helper->renderAsCollapse('TestPlugin');
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
            ['i' => ['class' => 'fas fa-home']],
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
            ['i' => ['class' => 'fas fa-flag']],
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
        $result = $this->Helper->renderAsDropdown('TestPlugin');

        //Checks array keys (menu names)
        $this->assertArrayKeysEqual(['TestPlugin.articles', 'TestPlugin.other_items'], $result);

        $expected = [
            ['a' => [
                'href' => '#',
                'aria-expanded' => 'false',
                'aria-haspopup' => 'true',
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'title' => 'First menu',
            ]],
            ['i' => ['class' => 'fas fa-home']],
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
            ['i' => ['class' => 'fas fa-flag']],
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
        $this->assertHtml($expected, implode(PHP_EOL, $result));
    }
}
