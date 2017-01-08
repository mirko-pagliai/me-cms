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
namespace MeCms\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * TreeBehaviorTest class
 */
class TreeBehaviorTest extends TestCase
{
    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.posts_categories',
    ];

    /**
     * Test for `findTreeList()` method
     * @test
     */
    public function testFindTreeList()
    {
        $table = TableRegistry::get('MeCms.PostsCategories');

        $query = $table->find('treeList');
        $this->assertEquals('Cake\ORM\Query', get_class($query));
        $this->assertEquals([
            1 => 'First post category',
            3 => '—Sub post category',
            4 => '——Sub sub post category',
            2 => 'Another post category',
        ], ($query->toArray()));

        $query = $table->find('treeList', ['spacer' => '_']);
        $this->assertEquals([
            1 => 'First post category',
            3 => '_Sub post category',
            4 => '__Sub sub post category',
            2 => 'Another post category',
        ], $query->toArray());
    }
}
