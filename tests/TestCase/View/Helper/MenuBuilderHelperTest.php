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

use Authentication\Identity;
use BadMethodCallException;
use Cake\Http\ServerRequest;
use Cake\View\View;
use MeCms\View\Helper\MenuBuilderHelper;
use MeTools\TestSuite\HelperTestCase;
use Tools\Exception\ObjectWrongInstanceException;

/**
 * MenuBuilderHelperTest class
 */
class MenuBuilderHelperTest extends HelperTestCase
{
    /**
     * @var \MeCms\View\Helper\MenuBuilderHelper
     */
    protected MenuBuilderHelper $Helper;

    /**
     * Called before every test method
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty($this->Helper)) {
            $Request = new ServerRequest();
            $Request = $Request->withAttribute('identity', new Identity(['group' => ['name' => 'admin']]));
            $this->Helper = new MenuBuilderHelper(new View($Request));
        }
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
        $this->assertEquals(['articles', 'empty_return', 'other_items'], $this->Helper->getMethods('TestPlugin'));
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
