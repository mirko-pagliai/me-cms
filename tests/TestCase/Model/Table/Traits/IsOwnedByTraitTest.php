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

namespace MeCms\Test\TestCase\Model\Table\Traits;

use MeCms\TestSuite\TestCase;

/**
 * IsOwnedByTraitTest class
 */
class IsOwnedByTraitTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $Posts;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Posts = $this->Posts ?: $this->getTable('MeCms.Posts');
    }

    /**
     * Test for `isOwnedBy()` method
     * @test
     */
    public function testIsOwnedBy(): void
    {
        $this->assertTrue($this->Posts->isOwnedBy(2, 4));
        $this->assertFalse($this->Posts->isOwnedBy(2, 1));
        $this->assertTrue($this->Posts->isOwnedBy(1, 1));
        $this->assertFalse($this->Posts->isOwnedBy(1, 2));
    }
}
