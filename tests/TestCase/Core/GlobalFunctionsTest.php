<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Core;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * GlobalFunctionsTest class
 */
class GlobalFunctionsTest extends TestCase
{
    /**
     * Test for `getConfig()` global function
     * @test
     */
    public function testGetConfig()
    {
        $this->assertNotEmpty(getConfig());
        $this->assertNotEmpty(getConfig(null));
        $this->assertNotEmpty(getConfig(null, null));

        $this->assertNull(getConfig('noExisting'));
        $this->assertEquals('defaultValue', getConfig('noExisting', 'defaultValue'));

        $this->assertNull(getConfig(ME_CMS . '.noExisting'));
        $this->assertEquals('defaultValue', getConfig(ME_CMS . '.noExisting', 'defaultValue'));

        Configure::write('exampleKey', 'exampleValue');
        $this->assertEquals('exampleValue', getConfig('exampleKey'));
        $this->assertEquals('exampleValue', getConfig('exampleKey', 'defaultValue'));

        Configure::write(ME_CMS . '.exampleKey', 'MeCmsExampleValue');
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
    public function testGetConfigOrFail()
    {
        Configure::write('exampleKey', 'exampleValue');
        $this->assertEquals('exampleValue', getConfigOrFail('exampleKey'));

        Configure::write(ME_CMS . '.exampleKey', 'MeCmsExampleValue');
        $this->assertEquals('MeCmsExampleValue', getConfigOrFail('exampleKey'));
    }

    /**
     * Test for `getConfigOrFail()` global function, with a no existing value
     * @expectedException RuntimeException
     * @test
     */
    public function testGetConfigOrFailNoExistingValue()
    {
        getConfigOrFail('noExisting');
    }
}
