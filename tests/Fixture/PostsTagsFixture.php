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
 * PostsTagsFixture
 */
class PostsTagsFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'tag_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'post_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
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
            'id' => 1,
            'tag_id' => 1,
            'post_id' => 1,
        ],
        [
            'id' => 2,
            'tag_id' => 2,
            'post_id' => 1,
        ],
        [
            'id' => 3,
            'tag_id' => 3,
            'post_id' => 1,
        ],
        [
            'id' => 4,
            'tag_id' => 1,
            'post_id' => 2,
        ],
        [
            'id' => 5,
            'tag_id' => 2,
            'post_id' => 2,
        ],
        [
            'id' => 6,
            'tag_id' => 1,
            'post_id' => 3,
        ],
        [
            'id' => 7,
            'tag_id' => 4,
            'post_id' => 5,
        ],
        [
            'id' => 8,
            'tag_id' => 1,
            'post_id' => 6,
        ],
    ];
}
