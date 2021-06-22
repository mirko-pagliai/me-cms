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

use MeTools\TestSuite\HelperTestCase;

/**
 * MenuBuilderHelperTest class
 * @property \MeCms\View\Helper\MenuBuilderHelper $Helper
 */
class MenuBuilderHelperTest extends HelperTestCase
{
    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadPlugins(['TestPlugin', 'TestPluginTwo']);
    }

    /**
     * Tests for `getMethods()` method
     * @test
     */
    public function testGetMethods(): void
    {
        $this->assertEquals([
            'posts',
            'pages',
            'users',
            'backups',
            'systems',
        ], $this->Helper->getMethods('MeCms'));
        $this->assertEquals(['articles', 'other_items'], $this->Helper->getMethods('TestPlugin'));
        $this->assertEquals([], $this->Helper->getMethods('TestPluginTwo'));
    }

    /**
     * Tests for `generate()` method
     * @test
     */
    public function testGenerate(): void
    {
        foreach (['MeCms', 'TestPlugin'] as $plugin) {
            $result = $this->Helper->generate($plugin);
            $this->assertNotEmpty($result);
            foreach ($result as $menu) {
                $this->assertArrayKeysEqual(['links', 'title', 'titleOptions', 'handledControllers'], $menu);
                $this->assertIsArrayNotEmpty($menu['links']);
            }
        }

        $this->assertSame([], $this->Helper->generate('TestPluginTwo'));
    }

    /**
     * Tests for `renderAsCollapse()` method
     * @test
     */
    public function testRenderAsCollapse(): void
    {
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
            ['div' => ['class' => 'collapse', 'data-parent' => '#my-container', 'id' => 'collapse-first-menu']],
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
            ['div' => ['class' => 'collapse', 'data-parent' => '#my-container', 'id' => 'collapse-second-menu']],
            ['a' => ['href' => '/', 'title' => 'Third link']],
            'Third link',
            '/a',
            ['a' => ['href' => '/', 'title' => 'Fourth link']],
            'Fourth link',
            '/a',
            '/div',
            '/div',
        ];
        $menus = $this->Helper->generate('TestPlugin');
        $result = '';
        foreach ($menus as $menu) {
            $result .= $this->Helper->renderAsCollapse($menu, 'my-container');
        }
        $this->assertHtml($expected, $result);

        //Sets the same controller that is handled by the menu
        $request = $this->Helper->getView()->getRequest()->withParam('controller', 'Articles');
        $this->Helper->getView()->setRequest($request);
        $menus = $this->Helper->generate('TestPlugin');
        $result = '';
        foreach ($menus as $menu) {
            $result .= $this->Helper->renderAsCollapse($menu, 'my-container');
        }
        $this->assertTextContains('<a href="#collapse-first-menu" aria-controls="collapse-first-menu" aria-expanded="true"', $result);
        $this->assertTextContains('<div class="collapse show"', $result);
    }

    /**
     * Tests for `renderAsDropdown()` method
     * @test
     */
    public function testRenderAsDropdown(): void
    {
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
        $result = $this->Helper->renderAsDropdown('TestPlugin');
        $this->assertHtml($expected, implode(PHP_EOL, $result));
    }
}
