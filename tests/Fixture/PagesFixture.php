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
 * PagesFixture
 */
class PagesFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'category_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'title' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'subtitle' => ['type' => 'string', 'length' => 150, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'slug' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'text' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'preview' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'latin1_swedish_ci', 'comment' => '', 'precision' => null],
        'enable_comments' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'priority' => ['type' => 'integer', 'length' => 1, 'unsigned' => false, 'null' => false, 'default' => '3', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'active' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
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
            'category_id' => 4,
            'title' => 'First page',
            'subtitle' => 'Subtitle for the first page',
            'slug' => 'first-page',
            'text' => '<b>Text of the first page</b>',
            'preview' => null,
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
            'preview' => null,
            'priority' => 1,
            'active' => 1,
            'created' => '2016-12-26 17:30:20',
            'modified' => '2016-12-26 17:30:20',
        ],
        [
            'id' => 3,
            'category_id' => 1,
            'title' => 'Disabled page',
            'subtitle' => 'Subtitle for the disabled page',
            'slug' => 'disabled-page',
            'text' => 'Text of the disabled page',
            'preview' => null,
            'priority' => 1,
            'active' => 0,
            'created' => '2016-12-26 18:30:20',
            'modified' => '2016-12-26 18:30:20',
        ],
    ];
}
