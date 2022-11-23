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
