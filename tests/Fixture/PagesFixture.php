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
     * @var array
     */
    public $records = [
        [
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
