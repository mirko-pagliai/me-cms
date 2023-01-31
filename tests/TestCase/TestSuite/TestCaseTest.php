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

use App\SkipTestCase;
use Cake\ORM\Locator\TableLocator;
use MeCms\Model\Table\PostsTable;
use MeCms\Test\TestCase\Model\Table\PostsTableTest;
use MeCms\TestSuite\TestCase;
use PHPUnit\Framework\AssertionFailedError;

/**
 * TestCaseTest class
 */
class TestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\TestCase::__get()
     */
    public function testGetMagicMethod(): void
    {
        $TableTest = new PostsTableTest();
        $this->assertSame('Posts', $TableTest->alias);
        $this->assertSame(PostsTable::class, $TableTest->originClassName);
        $this->assertInstanceOf(PostsTable::class, $TableTest->Table);

        //With a no existing property
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Property `noExistingProperty` does not exist');
        $TableTest->noExistingProperty;
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\TestCase::tearDown()
     */
    public function testTearDown(): void
    {
        $TableLocator = $this->createMock(TableLocator::class);
        $TableLocator->expects($this->atLeastOnce())->method('clear');

        /** @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject $Table */
        $Table = $this->getMockForModel('MeCms.Posts', ['clearCache']);
        $Table->expects($this->once())->method('clearCache');

        $TestCase = $this->getMockForAbstractClass(TestCase::class, [null, ['cache' => compact('Table')]], '', true, true, true, ['getTableLocator']);
        $TestCase->method('getTableLocator')->willReturn($TableLocator);

        $TestCase->tearDown();
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\TestCase::skipIfCakeIsLessThan()
     */
    public function testSkipIfCakeIsLessThan(): void
    {
        $result = (new SkipTestCase('testSkipIfCakeIsLessThanTrue'))->run();
        $this->assertSame(1, $result->skippedCount());

        $result = (new SkipTestCase('testSkipIfCakeIsLessThanFalse'))->run();
        $this->assertSame(0, $result->skippedCount());
    }
}
