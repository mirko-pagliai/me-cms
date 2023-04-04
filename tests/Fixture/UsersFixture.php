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
            'username' => 'epsilon',
            'email' => 'ypsilon@test.com',
            'password' => '',
            'first_name' => 'Epsilon',
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
            'password' => 'Zeta1!',
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
