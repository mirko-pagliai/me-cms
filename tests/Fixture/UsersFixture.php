<?php
/**
 * This file is part of MeTools.
 *
 * MeTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeTools.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    //@codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'group_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'username' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'email' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'password' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'first_name' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'last_name' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'banned' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'post_count' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'group_id' => ['type' => 'index', 'columns' => ['group_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'username' => ['type' => 'unique', 'columns' => ['username', 'email'], 'length' => []],
        ],
    ];
    //@codingStandardsIgnoreEnd

    /**
     * Records
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'group_id' => 1,
            'username' => 'alfa',
            'email' => 'alfa@test.com',
            'password' => '',
            'first_name' => 'Alfa',
            'last_name' => 'Beta',
            'active' => 1,
            'banned' => 0,
            'post_count' => 2,
            'created' => '2016-12-24 17:00:05',
        ],
        [
            'id' => 2,
            'group_id' => 3,
            'username' => 'gamma',
            'email' => 'gamma@test.com',
            'password' => '',
            'first_name' => 'Gamma',
            'last_name' => 'Delta',
            'active' => 0,
            'banned' => 0,
            'post_count' => 0,
            'created' => '2016-12-24 17:01:06',
        ],
        [
            'id' => 3,
            'group_id' => 3,
            'username' => 'ypsilon',
            'email' => 'ypsilon@test.com',
            'password' => '',
            'first_name' => 'Ypsilon',
            'last_name' => 'Zeta',
            'active' => 1,
            'banned' => 1,
            'post_count' => 0,
            'created' => '2016-12-24 17:02:10',
        ],
        [
            'id' => 4,
            'group_id' => 3,
            'username' => 'abc',
            'email' => 'abc@example.com',
            'password' => '',
            'first_name' => 'Abc',
            'last_name' => 'Def',
            'active' => 1,
            'banned' => 0,
            'post_count' => 1,
            'created' => '2016-12-24 17:03:10',
        ],
        [
            'id' => 5,
            'group_id' => 1,
            'username' => 'delta',
            'email' => 'delta@example.com',
            'password' => '',
            'first_name' => 'Mno',
            'last_name' => 'Pqr',
            'active' => 1,
            'banned' => 0,
            'post_count' => 0,
            'created' => '2016-12-24 17:04:10',
        ],
    ];
}
