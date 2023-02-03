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
use MeCms\Test\TestCase\View\Cell\PostsWidgetsCellTest;
use MeCms\TestSuite\TestCase;
use MeCms\View\Cell\PostsWidgetsCell;
use MeCms\View\Helper\WidgetHelper;

/**
 * CellTestCaseTest class
 */
class CellTestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\CellTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $CellTestCase = new PostsWidgetsCellTest();
        $this->assertSame('Posts', $CellTestCase->alias);
        $this->assertSame(PostsWidgetsCell::class, $CellTestCase->originClassName);
        $this->assertInstanceOf(PostsTable::class, $CellTestCase->Table);
        $this->assertInstanceOf(WidgetHelper::class, $CellTestCase->Widget);
    }
}
