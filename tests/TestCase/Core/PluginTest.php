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
namespace MeCms\Test\TestCase\Core;

use MeCms\Core\Plugin;
use MeTools\TestSuite\TestCase;

/**
 * PluginTest class.
 */
class PluginTest extends TestCase
{
    /**
     * Tests for `all()` method
     * @test
     */
    public function testAll()
    {
        $result = Plugin::all();
        $this->assertEquals(ME_CMS, $result[0]);
        $this->assertEquals(ME_TOOLS, $result[1]);
        $this->assertNotContains('TestPlugin', $result);

        Plugin::load('TestPlugin');

        $result = Plugin::all();
        $this->assertEquals(ME_CMS, $result[0]);
        $this->assertEquals(ME_TOOLS, $result[1]);
        $this->assertContains('TestPlugin', $result);

        $result = Plugin::all(['order' => false]);
        $this->assertNotEquals(ME_CMS, $result[0]);
        $this->assertNotEquals(ME_TOOLS, $result[1]);
        $this->assertContains(ME_CMS, $result);
        $this->assertContains(ME_TOOLS, $result);
        $this->assertContains('TestPlugin', $result);
    }
}
