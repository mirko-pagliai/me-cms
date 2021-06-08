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

use Cake\Core\Configure;
use MeCms\TestSuite\TestCase;
use RuntimeException;

/**
 * GlobalFunctionsTest class
 */
class GlobalFunctionsTest extends TestCase
{
    /**
     * Test for `getConfig()` global function
     * @test
     */
    public function testGetConfig(): void
    {
        $this->assertNotEmpty(getConfig());
        $this->assertNotEmpty(getConfig(null));
        $this->assertNotEmpty(getConfig(null, null));

        $this->assertNull(getConfig('noExisting'));
        $this->assertEquals('defaultValue', getConfig('noExisting', 'defaultValue'));

        $this->assertNull(getConfig('MeCms.noExisting'));
        $this->assertEquals('defaultValue', getConfig('MeCms.noExisting', 'defaultValue'));

        Configure::write('exampleKey', 'exampleValue');
        $this->assertEquals('exampleValue', getConfig('exampleKey'));
        $this->assertEquals('exampleValue', getConfig('exampleKey', 'defaultValue'));

        Configure::write('MeCms.exampleKey', 'MeCmsExampleValue');
        $this->assertEquals('MeCmsExampleValue', getConfig('exampleKey'));
        $this->assertEquals('MeCmsExampleValue', getConfig('exampleKey', 'defaultValue'));

        Configure::write('SomePlugin.exampleKey', 'SomePluginExampleValue');
        $this->assertEquals('SomePluginExampleValue', getConfig('SomePlugin.exampleKey'));
        $this->assertEquals('SomePluginExampleValue', getConfig('SomePlugin.exampleKey', 'defaultValue'));
    }

    /**
     * Test for `getConfigOrFail()` global function
     * @test
     */
    public function testGetConfigOrFail(): void
    {
        Configure::write('exampleKey', 'exampleValue');
        $this->assertEquals('exampleValue', getConfigOrFail('exampleKey'));

        Configure::write('MeCms.exampleKey', 'MeCmsExampleValue');
        $this->assertEquals('MeCmsExampleValue', getConfigOrFail('exampleKey'));

        //With a no existing value
        $this->expectException(RuntimeException::class);
        getConfigOrFail('noExisting');
    }
}
