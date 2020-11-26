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

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;
use Tools\Filesystem;

/**
 * BannersFixture
 */
class BannersFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'position_id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => null],
        'filename' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null],
        'target' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null],
        'description' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1'],
        'click_count' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => '0', 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null],
        '_indexes' => [
            'position_id' => ['type' => 'index', 'columns' => ['position_id'], 'length' => []],
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
            'position_id' => 1,
            'filename' => 'banner1.jpg',
            'target' => 'http://www.example.com',
            'description' => 'First banner',
            'active' => 1,
            'click_count' => 2,
            'created' => '2016-12-26 16:26:04',
            'modified' => '2016-12-26 16:26:04',
        ],
        [
            'position_id' => 1,
            'filename' => 'banner2.jpg',
            'target' => '',
            'description' => 'Second banner',
            'active' => 0,
            'click_count' => 0,
            'created' => '2016-12-26 16:27:04',
            'modified' => '2016-12-26 16:27:04',
        ],
        [
            'position_id' => 2,
            'filename' => 'banner3.jpg',
            'target' => '',
            'description' => 'Third banner',
            'active' => 1,
            'click_count' => 3,
            'created' => '2016-12-26 16:28:04',
            'modified' => '2016-12-26 16:28:04',
        ],
    ];

    /**
     * Run after all tests executed, should remove the table/collection from
     *  the connection
     * @param ConnectionInterface $db An instance of the connection the fixture
     *  should be removed from
     * @return bool
     */
    public function drop(ConnectionInterface $db): bool
    {
        (new Filesystem())->unlinkRecursive(BANNERS, 'empty', true);

        return parent::drop($db);
    }

    /**
     * Run before each test is executed
     * @param ConnectionInterface $db An instance of the connection into which
     *  the records will be inserted
     * @return \Cake\Database\StatementInterface|bool on success or if there are
     *  no records to insert, or `false` on failure
     */
    public function insert(ConnectionInterface $db)
    {
        foreach ($this->records as $record) {
            (new Filesystem())->createFile(BANNERS . $record['filename'], null, 0777, true);
        }

        return parent::insert($db);
    }
}
