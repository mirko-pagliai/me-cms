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
use MeCms\Model\Entity\User;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'group_id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => null],
        'username' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null],
        'email' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null],
        'password' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
        'first_name' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null],
        'last_name' => ['type' => 'string', 'length' => 40, 'null' => false, 'default' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1'],
        'banned' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0'],
        'post_count' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => '0', 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        '_indexes' => [
            'group_id' => ['type' => 'index', 'columns' => ['group_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'username' => ['type' => 'unique', 'columns' => ['username', 'email'], 'length' => []],
        ],
    ];

    /**
     * Records
     * @var array
     */
    public $records = [
        [
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

    /**
     * Initialize the fixture
     * @return void
     */
    public function init(): void
    {
        $user = new User([
            'group_id' => 1,
            'username' => 'zeta',
            'email' => 'zeta@example.com',
            'password' => 'zeta',
            'first_name' => 'Zeta',
            'last_name' => 'Zeta',
            'active' => 1,
            'banned' => 0,
            'post_count' => 0,
            'created' => '2016-12-24 17:05:10',
        ]);

        $this->records[] = $user->toArray();

        parent::init();
    }
}
