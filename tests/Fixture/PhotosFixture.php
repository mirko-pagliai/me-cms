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

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * PhotosFixture
 */
class PhotosFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    //@codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'album_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'filename' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'size' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'description' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'album_id' => ['type' => 'index', 'columns' => ['album_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'filename' => ['type' => 'unique', 'columns' => ['filename'], 'length' => []],
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
            'album_id' => 1,
            'filename' => 'photo1.jpg',
            'size' => null,
            'description' => 'A photo',
            'active' => 1,
            'created' => '2016-12-28 10:38:42',
            'modified' => '2016-12-28 10:38:42'
        ],
        [
            'id' => 2,
            'album_id' => 2,
            'filename' => 'photoa.jpg',
            'size' => null,
            'description' => 'Another photo',
            'active' => 1,
            'created' => '2016-12-28 10:39:42',
            'modified' => '2016-12-28 10:39:42'
        ],
        [
            'id' => 3,
            'album_id' => 1,
            'filename' => 'photo3.jpg',
            'size' => null,
            'description' => 'Third photo',
            'active' => 1,
            'created' => '2016-12-28 10:40:42',
            'modified' => '2016-12-28 10:40:42'
        ],
        [
            'id' => 4,
            'album_id' => 2,
            'filename' => 'photo4.jpg',
            'size' => null,
            'description' => 'No active photo',
            'active' => 0,
            'created' => '2016-12-28 10:41:42',
            'modified' => '2016-12-28 10:41:42'
        ],
    ];

    /**
     * Run after all tests executed, should remove the table/collection from
     *  the connection
     * @param ConnectionInterface $db An instance of the connection the fixture
     *  should be removed from
     * @return void
     */
    public function drop(ConnectionInterface $db)
    {
        parent::drop($db);

        foreach (glob(PHOTOS . '*/*.*') as $file) {
            unlink($file);
        }

        foreach (glob(PHOTOS . '*', GLOB_ONLYDIR) as $dir) {
            rmdir($dir);
        }
    }

    /**
     * Run before each test is executed
     * @param ConnectionInterface $db An instance of the connection into which
     *  the records will be inserted
     * @return void
     */
    public function insert(ConnectionInterface $db)
    {
        parent::insert($db);

        foreach ($this->records as $record) {
            $file = PHOTOS . $record['album_id'] . DS . $record['filename'];

            if (!file_exists($file)) {
                if (!file_exists(dirname($file))) {
                    mkdir(dirname($file));
                }

                copy(WWW_ROOT . 'img' . DS . 'image.jpg', $file);
            }
        }
    }
}
