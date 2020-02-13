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
 * PostsTagsFixture
 */
class PostsTagsFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'tag_id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => null],
        'post_id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => null],
        '_indexes' => [
            'tag_id' => ['type' => 'index', 'columns' => ['tag_id'], 'length' => []],
            'post_id' => ['type' => 'index', 'columns' => ['post_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ];

    /**
     * Records
     * @var array
     */
    public $records = [
        [
            'tag_id' => 1,
            'post_id' => 1,
        ],
        [
            'tag_id' => 2,
            'post_id' => 1,
        ],
        [
            'tag_id' => 3,
            'post_id' => 1,
        ],
        [
            'tag_id' => 1,
            'post_id' => 2,
        ],
        [
            'tag_id' => 2,
            'post_id' => 2,
        ],
        [
            'tag_id' => 1,
            'post_id' => 3,
        ],
        [
            'tag_id' => 4,
            'post_id' => 5,
        ],
        [
            'tag_id' => 1,
            'post_id' => 6,
        ],
    ];
}
