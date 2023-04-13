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

use BadMethodCallException;
use Cake\View\Helper;
use Cake\View\View;
use MeCms\View\Helper\IdentityHelper;
use MeCms\View\Helper\MenuBuilderHelper;
use MeTools\TestSuite\HelperTestCase;
use Tools\Exception\ObjectWrongInstanceException;

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

        /**
         * Mock for `loadHelper()` method.
         * It makes the `Menu` helpers use an `IdentityHelper` stub.
         * @see \Cake\View\View::loadHelper()
         */
        $View = $this->createPartialMock(View::class, ['loadHelper']);
        $View->method('loadHelper')->willReturnCallback(function (string $name, array $config = []) use ($View): Helper {
            [, $class] = pluginSplit($name);
            $Helper = $View->helpers()->load($name, $config);
            if (str_ends_with($name, 'Menu')) {
                /** @var \MeCms\View\Helper\AbstractMenuHelper $Helper */
                $Helper->Identity = $this->createStub(IdentityHelper::class);
            }

            return $View->{$class} = $Helper;
        });

        $this->Helper = new MenuBuilderHelper($View);
    }

    /**
     * @test
     * @uses \MeCms\View\Helper\MenuBuilderHelper::getMethods()
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
     * @test
     * @uses \MeCms\View\Helper\MenuBuilderHelper::generate()
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

        // Class `TestPluginThree\View\Helper\MenuHelper` does not extend `AbstractMenuHelper`
        $this->assertException(fn() => $this->Helper->generate('TestPluginThree'), ObjectWrongInstanceException::class);

        // Method `TestPluginTwo\View\Helper\MenuHelper::badArticles()` returns a bad value
        $this->assertException(fn() => $this->Helper->generate('TestPluginTwo'), BadMethodCallException::class, 'Method `TestPluginTwo\View\Helper\MenuHelper::badArticles()` returned only 1 values');
    }
}
