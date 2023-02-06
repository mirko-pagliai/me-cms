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
use MeCms\Test\TestCase\Model\Table\UsersTableTest;
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

    /**
     * @test
     * @uses \MeCms\TestSuite\TableTestCase::assertBelongsTo()
     */
    public function testAssertBelongsTo(): void
    {
        $TableTestCase = new PostsTableTest();
        $TableTestCase->assertBelongsTo($TableTestCase->Table->Categories);

        $this->expectAssertionFailed('Failed asserting that `MeCms\Model\Table\TagsTable` is an instance of `Cake\ORM\Association\BelongsTo`');
        $TableTestCase->assertBelongsTo($TableTestCase->Table->Tags);
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\TableTestCase::assertBelongsToMany()
     */
    public function testAssertBelongsToMany(): void
    {
        $TableTestCase = new PostsTableTest();
        $TableTestCase->assertBelongsToMany($TableTestCase->Table->Tags);

        $this->expectAssertionFailed('Failed asserting that `MeCms\Model\Table\PostsCategoriesTable` is an instance of `Cake\ORM\Association\BelongsToMany`');
        $TableTestCase->assertBelongsToMany($TableTestCase->Table->Categories);
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\TableTestCase::assertHasBehavior()
     */
    public function testAssertHasBehavior(): void
    {
        $TableTestCase = new PostsTableTest();
        $TableTestCase->assertHasBehavior('Timestamp');
        $TableTestCase->assertHasBehavior(['CounterCache', 'Timestamp']);

        $this->expectAssertionFailed('Failed asserting that `MeCms\Model\Table\PostsTable` has `NoExistingBehavior` behavior');
        $TableTestCase->assertHasBehavior(['CounterCache', 'NoExistingBehavior']);
    }

    /**
     * @test
     * @uses \MeCms\TestSuite\TableTestCase::assertHasMany()
     */
    public function testAssertHasMany(): void
    {
        $TableTestCase = new UsersTableTest();
        $TableTestCase->assertHasMany($TableTestCase->Table->Posts);

        $this->expectAssertionFailed('Failed asserting that `MeCms\Model\Table\UsersGroupsTable` is an instance of `Cake\ORM\Association\HasMany`');
        $TableTestCase->assertHasMany($TableTestCase->Table->Groups);
    }
}
