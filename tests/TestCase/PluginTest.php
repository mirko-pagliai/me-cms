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
     * @var \MeCms\Plugin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $Plugin;

    /**
     * @var \Cake\Http\BaseApplication|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $app;

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->app = $this->getMockForAbstractClass(BaseApplication::class, ['']);

        $this->Plugin = $this->getMockBuilder(MeCms::class)
            ->setMethods(['isCli'])
            ->getMock();
    }

    /**
     * Tests for `bootstrap()` method
     * @test
     */
    public function testBootstrap()
    {
        /**
         * Internal functions. Returns an array of loaded plugins
         */
        $getLoadedPlugins = function () {
            return array_keys(iterator_to_array($this->app->getPlugins()));
        };

        $this->Plugin->expects($this->at(0))
            ->method('isCli')
            ->will($this->returnValue(true));
        $this->Plugin->expects($this->at(1))
            ->method('isCli')
            ->will($this->returnValue(false));

        //Now is cli
        $expected = [
            'Assets',
            'MeTools',
            'DatabaseBackup',
            'Recaptcha',
            'RecaptchaMailhide',
            'StopSpam',
            'Thumber',
            'Tokens',
        ];
        $this->app->getPlugins()->clear();
        $this->Plugin->bootstrap($this->app);
        $this->assertEquals($expected, $getLoadedPlugins());
        $this->assertContains(getConfig('Assets.target'), Configure::read('WRITABLE_DIRS'));

        //Now is not cli
        $expectedDiff = ['DebugKit', 'WyriHaximus/MinifyHtml'];
        $this->app->getPlugins()->clear();
        $this->Plugin->bootstrap($this->app);
        $this->assertEquals($expectedDiff, array_values(array_diff($getLoadedPlugins(), $expected)));
    }

    /**
     * Tests for `setWritableDirs()` method
     * @test
     */
    public function testSetWritableDirs()
    {
        Configure::delete('Assets.target');
        $this->invokeMethod($this->Plugin, 'setWritableDirs');
        $this->assertNotContains(getConfig('Assets.target'), Configure::read('WRITABLE_DIRS'));
    }
}
