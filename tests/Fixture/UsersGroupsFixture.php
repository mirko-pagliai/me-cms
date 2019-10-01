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
namespace MeCms\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersGroupsFixture
 */
class UsersGroupsFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'name' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => null],
        'label' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => null],
        'description' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null],
        'user_count' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => '0', 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'name' => ['type' => 'unique', 'columns' => ['name', 'label'], 'length' => []],
        ],
    ];

    /**
     * Records
     * @var array
     */
    public $records = [
        [
            'name' => 'admin',
            'label' => 'Admin',
            'description' => '',
            'user_count' => 2,
            'created' => '2016-12-24 17:00:05',
        ],
        [
            'name' => 'manager',
            'label' => 'Manager',
            'description' => '',
            'user_count' => 0,
            'created' => '2016-12-24 17:01:05',
        ],
        [
            'name' => 'user',
            'label' => 'User',
            'description' => '',
            'user_count' => 3,
            'created' => '2016-12-24 17:02:05',
        ],
        [
            'name' => 'fans',
            'label' => 'Fans',
            'description' => '',
            'user_count' => 3,
            'created' => '2016-12-24 17:03:05',
        ],
        [
            'name' => 'people',
            'label' => 'People',
            'description' => '',
            'user_count' => 0,
            'created' => '2016-12-24 17:04:05',
        ],
    ];
}
