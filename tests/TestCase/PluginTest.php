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
namespace MeCms\TestCase;

use Cake\Http\BaseApplication;
use MeCms\Plugin;
use MeCms\TestSuite\TestCase;

/**
 * PluginTest class
 */
class PluginTest extends TestCase
{
    /**
     * Tests for `bootstrap()` method
     * @test
     */
    public function testBootstrap()
    {
        $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);
        $app->getPlugins()->clear();
        $getLoadedPlugins = function () use ($app) {
            return array_keys(iterator_to_array($app->getPlugins()));
        };

        $plugin = $this->getMockBuilder(Plugin::class)
            ->setMethods(['setVendorLinks', 'setWritableDirs'])
            ->getMock();
        $this->assertEmpty($getLoadedPlugins());
        $plugin->bootstrap($app);

        $expected = [
            'Assets',
            'DatabaseBackup',
            'MeTools',
            'Recaptcha',
            'RecaptchaMailhide',
            'Thumber',
            'Tokens',
        ];
        $this->assertEquals($expected, $getLoadedPlugins());

        $app->getPlugins()->clear();
        $this->assertEmpty($getLoadedPlugins());
        $plugin = $this->getMockBuilder(Plugin::class)
            ->setMethods(['isCli', 'setVendorLinks', 'setWritableDirs'])
            ->getMock();
        $plugin->method('isCli')->will($this->returnValue(false));
        $plugin->bootstrap($app);

        $expectedDiff = [
            'DebugKit',
            'CommonMark',
            'WyriHaximus/MinifyHtml',
        ];
        $this->assertEquals($expectedDiff, array_values(array_diff($getLoadedPlugins(), $expected)));
    }
}
