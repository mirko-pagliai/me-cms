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
use MeCms\Model\Validation\PostValidator;
use MeCms\Test\TestCase\Model\Validation\PostValidatorTest;
use MeCms\TestSuite\TestCase;

/**
 * ValidationTestCaseTest class
 */
class ValidationTestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\ValidationTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $ValidationTestCase = new PostValidatorTest();
        $this->assertSame('Posts', $ValidationTestCase->alias);
        $this->assertSame(PostValidator::class, $ValidationTestCase->originClassName);
        $this->assertInstanceOf(PostsTable::class, $ValidationTestCase->Table);
    }
}
