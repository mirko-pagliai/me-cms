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

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PagesFixture
 */
class PagesFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    //@codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'category_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'title' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'subtitle' => ['type' => 'string', 'length' => 150, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'slug' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'text' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'preview' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'priority' => ['type' => 'integer', 'length' => 1, 'unsigned' => false, 'null' => false, 'default' => '3', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
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
            'title' => 'First page',
            'subtitle' => 'Subtitle for the first page',
            'slug' => 'first-page',
            'text' => 'Text of the first page',
            'preview' => '',
            'priority' => 1,
            'active' => 1,
            'created' => '2016-12-26 17:29:20',
            'modified' => '2016-12-26 17:29:20',
        ],
        [
            'id' => 2,
            'category_id' => 1,
            'title' => 'Second page',
            'subtitle' => 'Subtitle for the second page',
            'slug' => 'second-page',
            'text' => 'Text of the second page',
            'preview' => '',
            'priority' => 1,
            'active' => 1,
            'created' => '2016-12-26 17:30:20',
            'modified' => '2016-12-26 17:30:20',
        ],
    ];
}
