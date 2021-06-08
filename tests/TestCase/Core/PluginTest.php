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

namespace MeCms\Test\TestCase\Core;

use MeCms\Core\Plugin;
use MeCms\TestSuite\TestCase;

/**
 * PluginTest class.
 */
class PluginTest extends TestCase
{
    /**
     * Tests for `all()` method
     * @test
     */
    public function testAll(): void
    {
        $this->removePlugins(['TestPlugin']);

        $result = Plugin::all();
        $this->assertEquals('MeCms', $result[0]);
        $this->assertEquals('MeTools', $result[1]);
        $this->assertNotContains('TestPlugin', $result);

        $this->loadPlugins(['TestPlugin']);
        $result = Plugin::all();
        $this->assertEquals('MeCms', $result[0]);
        $this->assertEquals('MeTools', $result[1]);
        $this->assertContains('TestPlugin', $result);

        $this->assertSame($result, Plugin::all(['mecms_core' => true]));

        $result = Plugin::all(['order' => false]);
        $this->assertNotEquals('MeCms', $result[0]);
        $this->assertNotEquals('MeTools', $result[1]);
        $this->assertContains('MeCms', $result);
        $this->assertContains('MeTools', $result);
        $this->assertContains('TestPlugin', $result);

        $result = Plugin::all(['mecms_core' => false]);
        $this->assertSame($result, ['MeCms', 'TestPlugin']);

        $result = Plugin::all(['exclude' => 'TestPlugin', 'mecms_core' => false]);
        $this->assertSame($result, ['MeCms']);
    }
}
