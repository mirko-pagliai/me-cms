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

use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * PostsFixture
 */
class PostsFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'user_id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => null],
        'category_id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => '1', 'autoIncrement' => null],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
        'subtitle' => ['type' => 'string', 'length' => 150, 'null' => true, 'default' => null],
        'slug' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
        'text' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null],
        'preview' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null],
        'enable_comments' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1'],
        'priority' => ['type' => 'integer', 'length' => 1, 'null' => false, 'default' => '3', 'autoIncrement' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1'],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
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
            'category_id' => 4,
            'user_id' => 1,
            'title' => 'First post',
            'slug' => 'first-post',
            'subtitle' => 'Subtitle for first post',
            'text' => '<b>Text of the first post</b>',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-11-28 18:55:19',
            'modified' => '2016-11-28 18:55:19',
            'active' => 1,
        ],
        //See `init()` for record 2
        [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Third post',
            'slug' => 'third-post',
            'subtitle' => 'Subtitle for third post',
            'text' => 'Text of the third post',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-12-28 18:57:19',
            'modified' => '2016-12-28 18:57:19',
            'active' => 1,
        ],
        [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Fourth post',
            'slug' => 'fourth-post',
            'subtitle' => 'Subtitle for fourth post',
            'text' => 'Text of the fourth post<!-- read-more -->Text after "read more"',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-12-28 18:58:19',
            'modified' => '2016-12-28 18:58:19',
            'active' => 1,
        ],
        [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Fifth post',
            'slug' => 'fifth-post',
            'subtitle' => 'Subtitle for fifth post',
            'text' => 'Text of the fifth post',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-12-28 18:59:19',
            'modified' => '2016-12-28 18:59:19',
            'active' => 1,
        ],
        [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Inactive post',
            'slug' => 'inactive-post',
            'subtitle' => 'Subtitle for inactive post',
            'text' => 'Text of the inactive post',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-12-28 19:00:19',
            'modified' => '2016-12-28 19:00:19',
            'active' => 0,
        ],
        [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Seventh post',
            'slug' => 'seventh-post',
            'subtitle' => 'Subtitle for seventh post',
            'text' => 'Text of the seventh post',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-12-29 18:59:19',
            'modified' => '2016-12-29 18:59:19',
            'active' => 1,
        ],
        //See `init()` for last record
    ];

    /**
     * Initialize the fixture
     */
    public function init()
    {
        //Adds a post with preview
        $preview = json_encode([new Entity(['url' => 'image.jpg', 'width' => 400, 'height' => 400])]);
        array_splice($this->records, 1, 0, [[
            'category_id' => 4,
            'user_id' => 4,
            'title' => 'Second post',
            'slug' => 'second-post',
            'subtitle' => 'Subtitle for second post',
            'text' => '<img src="image.jpg" />Text of the second post',
            'preview' => $preview,
            'priority' => 1,
            'created' => '2016-12-28 18:56:19',
            'modified' => '2016-12-28 18:56:19',
            'active' => 1,
        ]]);

        //Adds a future post
        $future = new Time('+999 days');
        $this->records[] = [
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Future post',
            'slug' => 'future-post',
            'subtitle' => 'Subtitle for future post',
            'text' => 'Text of the future post',
            'preview' => null,
            'priority' => 1,
            'created' => $future,
            'modified' => $future,
            'active' => 1,
        ];

        parent::init();
    }
}
