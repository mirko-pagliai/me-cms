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

namespace MeCms\Test\TestCase\Model\Table;

use MeCms\Model\Validation\PageValidator;
use MeCms\TestSuite\PostsAndPagesTablesTestCase;

/**
 * PagesTableTest class
 */
class PagesTableTest extends PostsAndPagesTablesTestCase
{
    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.PagesCategories',
    ];

    /**
     * Test for `initialize()` method
     * @test
     */
    public function testInitialize(): void
    {
        parent::testInitialize();

        $this->assertEquals('pages', $this->Table->getTable());

        $this->assertInstanceOf(PageValidator::class, $this->Table->getValidator());
    }
}
