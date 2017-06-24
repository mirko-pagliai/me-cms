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

use Cake\TestSuite\TestCase;
use MeCms\Core\Plugin;

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
        $this->assertEquals([
            ME_CMS,
            METOOLS,
            ASSETS,
            DATABASE_BACKUP,
            THUMBER,
            'Tokens',
        ], Plugin::all());

        Plugin::load('TestPlugin');

        $this->assertEquals([
            ME_CMS,
            METOOLS,
            ASSETS,
            DATABASE_BACKUP,
            'TestPlugin',
            THUMBER,
            'Tokens',
        ], Plugin::all());

        $this->assertEquals([
            ASSETS,
            DATABASE_BACKUP,
            ME_CMS,
            METOOLS,
            'TestPlugin',
            THUMBER,
            'Tokens',
        ], Plugin::all(['order' => false]));
    }
}
