<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadPlugins(['TestPlugin' => []]);
    }

    /**
     * Tests for `getMethods()` method
     * @uses \MeCms\View\Helper\MenuBuilderHelper::getMethods()
     * @test
     */
    public function testGetMethods(): void
    {
        $this->assertEquals([
            'posts',
            'pages',
            'users',
            'systems',
        ], $this->Helper->getMethods('MeCms'));
        $this->assertEquals(['articles', 'other_items'], $this->Helper->getMethods('TestPlugin'));
        $this->assertEquals(['badArticles'], $this->Helper->getMethods('TestPluginTwo'));
    }

    /**
     * Tests for `generate()` method
     * @uses \MeCms\View\Helper\MenuBuilderHelper::generate()
     * @test
     */
    public function testGenerate(): void
    {
        $this->loadPlugins(['TestPlugin' => [], 'TestPluginTwo' => []]);

        foreach (['MeCms', 'TestPlugin'] as $plugin) {
            $result = $this->Helper->generate($plugin);
            $this->assertNotEmpty($result);
            foreach ($result as $menu) {
                $this->assertArrayKeysEqual(['links', 'title', 'titleOptions', 'handledControllers'], $menu);
                $this->assertIsArrayNotEmpty($menu['links']);
            }
        }

        $this->expectExceptionMessage('Method `TestPluginTwo\View\Helper\MenuHelper::badArticles()` returned only 1 values');
        $this->Helper->generate('TestPluginTwo');
    }
}
