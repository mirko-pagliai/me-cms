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
use MeCms\Plugin as MeCms;
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

        $expected = [
            'Assets',
            'MeTools',
            'DatabaseBackup',
            'Recaptcha',
            'RecaptchaMailhide',
            'Thumber',
            'Tokens',
        ];
        $plugin = $this->getMockBuilder(MeCms::class)
            ->setMethods(['setVendorLinks', 'setWritableDirs'])
            ->getMock();
        $plugin->bootstrap($app);
        $this->assertEquals($expected, $getLoadedPlugins());

        $expectedDiff = [
            'DebugKit',
            'CommonMark',
            'WyriHaximus/MinifyHtml',
        ];
        $plugin = $this->getMockBuilder(MeCms::class)
            ->setMethods(['isCli', 'setVendorLinks', 'setWritableDirs'])
            ->getMock();
        $plugin->method('isCli')->will($this->returnValue(false));
        $plugin->bootstrap($app);
        $this->assertEquals($expectedDiff, array_values(array_diff($getLoadedPlugins(), $expected)));
    }
}
