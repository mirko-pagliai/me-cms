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

use Cake\I18n\Time;
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
    //@codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'category_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'slug' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'subtitle' => ['type' => 'string', 'length' => 150, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'text' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'preview' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'priority' => ['type' => 'integer', 'length' => 1, 'unsigned' => false, 'null' => false, 'default' => '3', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        '_indexes' => [
            'created_at' => ['type' => 'index', 'columns' => ['created'], 'length' => []],
            'category_id' => ['type' => 'index', 'columns' => ['category_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'slug' => ['type' => 'unique', 'columns' => ['slug'], 'length' => []],
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
            'category_id' => 4,
            'user_id' => 1,
            'title' => 'First post',
            'slug' => 'first-post',
            'subtitle' => 'Subtitle for first post',
            'text' => 'Text of the first post',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-11-28 18:55:19',
            'modified' => '2016-11-28 18:55:19',
            'active' => 1,
        ],
        [
            'id' => 2,
            'category_id' => 4,
            'user_id' => 4,
            'title' => 'Second post',
            'slug' => 'second-post',
            'subtitle' => 'Subtitle for second post',
            'text' => '<img src="https://raw.githubusercontent.com/mirko-pagliai/me-cms/master/tests/test_app/TestApp/webroot/img/image.jpg" />Text of the second post',
            'preview' => '{"preview":"https:\/\/raw.githubusercontent.com\/mirko-pagliai\/me-cms\/master\/tests\/test_app\/TestApp\/webroot\/img\/image.jpg","width":400,"height":400}',
            'priority' => 1,
            'created' => '2016-12-28 18:56:19',
            'modified' => '2016-12-28 18:56:19',
            'active' => 1,
        ],
        [
            'id' => 3,
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
            'id' => 4,
            'category_id' => 1,
            'user_id' => 1,
            'title' => 'Fourth post',
            'slug' => 'fourth-post',
            'subtitle' => 'Subtitle for fourth post',
            'text' => 'Text of the fourth post',
            'preview' => null,
            'priority' => 1,
            'created' => '2016-12-28 18:58:19',
            'modified' => '2016-12-28 18:58:19',
            'active' => 1,
        ],
        [
            'id' => 5,
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
            'id' => 6,
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
            'id' => 7,
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
    ];

    /**
     * Initialize the fixture
     */
    public function init()
    {
        $future = new Time('+999 days');

        //Adds a future post
        $this->records[] = [
            'id' => collection($this->records)->extract('id')->last() + 1,
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
