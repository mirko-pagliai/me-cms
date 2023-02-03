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

namespace MeCms\Test\TestCase;

use Cake\Core\Configure;
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
     * @uses \MeCms\Plugin::bootstrap()
     * @test
     */
    public function testBootstrap(): void
    {
        /**
         * Internal functions. Returns an array of loaded plugins
         */
        $getLoadedPlugins = fn(BaseApplication $app): array => array_keys(iterator_to_array($app->getPlugins()));

        Configure::write('MeCms.default.theme', 'MyTheme');
        $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);

        //In the first call is cli
        $Plugin = $this->createPartialMock(MeCms::class, ['isCli']);
        $Plugin->method('isCli')->willReturn(true);

        $Plugin->bootstrap($app);
        $loadedPlugins = $getLoadedPlugins($app);
        $this->assertContains('MyTheme', $loadedPlugins);

        $Plugin = $this->createPartialMock(MeCms::class, ['isCli']);
        $Plugin->method('isCli')->willReturn(false);
        $Plugin->bootstrap($app);

        //In the second call is not cli
        $Plugin->bootstrap($app);
        $this->assertEquals([...$loadedPlugins, 'WyriHaximus/MinifyHtml'], $getLoadedPlugins($app));
    }
}
