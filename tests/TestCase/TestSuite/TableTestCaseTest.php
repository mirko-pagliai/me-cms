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

namespace MeCms\Test\TestCase\TestSuite;

use MeCms\Model\Table\PostsTable;
use MeCms\Test\TestCase\Model\Table\PostsTableTest;
use MeCms\TestSuite\TestCase;

/**
 * TableTestCaseTest class
 */
class TableTestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\TableTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $TableTestCase = new PostsTableTest();
        $this->assertSame('Posts', $TableTestCase->alias);
        $this->assertSame(PostsTable::class, $TableTestCase->originClassName);
        $this->assertInstanceOf(PostsTable::class, $TableTestCase->Table);
    }
}
