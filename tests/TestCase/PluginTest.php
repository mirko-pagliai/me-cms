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

namespace MeCms\TestCase;

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
     * @test
     */
    public function testBootstrap()
    {
        /**
         * Internal functions. Returns an array of loaded plugins
         */
        $getLoadedPlugins = function ($app) {
            return array_keys(iterator_to_array($app->getPlugins()));
        };

        Configure::write('MeCms.default.theme', 'MyTheme');
        $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);

        $Plugin = $this->getMockBuilder(MeCms::class)
            ->setMethods(['isCli'])
            ->getMock();

        $Plugin->expects($this->at(0))
            ->method('isCli')
            ->will($this->returnValue(true));
        $Plugin->expects($this->at(1))
            ->method('isCli')
            ->will($this->returnValue(false));

        //Now is cli
        $Plugin->bootstrap($app);
        $loadedPlugins = $getLoadedPlugins($app);
        $this->assertContains('MyTheme', $loadedPlugins);
        $this->assertNotEmpty($loadedPlugins);

        //Now is not cli
        $expectedDiff = ['DebugKit', 'WyriHaximus/MinifyHtml'];
        $Plugin->bootstrap($app);
        $this->assertEquals($expectedDiff, array_values(array_diff($getLoadedPlugins($app), $loadedPlugins)));
    }
}
