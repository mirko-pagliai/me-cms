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
 * BannersPositionsFixture
 */
class BannersPositionsFixture extends TestFixture
{
    /**
     * Fields
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => null, 'autoIncrement' => true],
        'title' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null],
        'description' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null],
        'banner_count' => ['type' => 'integer', 'length' => 11, 'null' => false, 'default' => '0', 'autoIncrement' => null],
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
            'title' => 'top',
            'description' => 'Top Banners',
            'banner_count' => 2,
            'created' => '2016-12-26 16:26:20',
            'modified' => '2016-12-26 16:26:20',
        ],
        [
            'title' => 'left',
            'description' => 'Left Banners',
            'banner_count' => 1,
            'created' => '2016-12-26 16:27:20',
            'modified' => '2016-12-26 16:27:20',
        ],
        [
            'title' => 'bottom',
            'description' => 'Bottom Banners',
            'banner_count' => 0,
            'created' => '2016-12-26 16:28:20',
            'modified' => '2016-12-26 16:28:20',
        ],
    ];
}
