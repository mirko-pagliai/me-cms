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

namespace MeCms\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersGroupsFixture
 */
class UsersGroupsFixture extends TestFixture
{
    /**
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
