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

use MeCms\Model\Entity\Post;
use MeCms\Test\TestCase\Model\Entity\PostTest;
use MeCms\TestSuite\TestCase;

/**
 * EntityTestCaseTest class
 */
class EntityTestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\CellTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $EntityTestClass = new PostTest();
        $this->assertSame('Posts', $EntityTestClass->alias);
        $this->assertSame(Post::class, $EntityTestClass->originClassName);
        $this->assertInstanceOf(Post::class, $EntityTestClass->Entity);
    }
}
