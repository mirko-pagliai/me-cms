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

namespace MeCms\Test\TestCase\View\Helper;

use MeCms\View\Helper\IdentityHelper;
use MeTools\TestSuite\HelperTestCase;

/**
 * IdentityHelperTest class
 * @property \MeCms\View\Helper\IdentityHelper $Helper
 */
class IdentityHelperTest extends HelperTestCase
{
    /**
     * @var bool
     */
    protected bool $autoInitializeClass = false;

    /**
     * Tests for `isGroup()` method
     * @uses \MeCms\View\Helper\IdentityHelper::isGroup()
     * @test
     */
    public function testIsGroup(): void
    {
        $Helper = $this->createPartialMock(IdentityHelper::class, ['get']);
        $Helper->method('get')->willReturnCallback(fn(?string $key = null): ?string => $key === 'group.name' ? 'admin' : null);

        $this->assertTrue($Helper->isGroup('admin'));
        $this->assertTrue($Helper->isGroup('admin', 'manager'));
        $this->assertFalse($Helper->isGroup('manager'));
        $this->assertFalse($Helper->isGroup('manager', 'otherGroup'));
    }
}
