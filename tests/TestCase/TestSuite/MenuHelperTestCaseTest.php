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

use MeCms\Test\TestCase\View\Helper\MenuHelperTest;
use MeCms\TestSuite\TestCase;
use MeCms\View\Helper\MenuHelper;

/**
 * MenuHelperTestCaseTest class
 */
class MenuHelperTestCaseTest extends TestCase
{
    /**
     * @test
     * @uses \MeCms\TestSuite\MenuHelperTestCase::__get()
     */
    public function testMagicGetMethod(): void
    {
        $MenuHelperTest = new MenuHelperTest();
        $this->assertSame('Menu', $MenuHelperTest->alias);
        $this->assertSame(MenuHelper::class, $MenuHelperTest->originClassName);
        $this->assertInstanceOf(MenuHelper::class, $MenuHelperTest->Helper);
        $this->assertIsMock($MenuHelperTest->Helper);
    }
}
