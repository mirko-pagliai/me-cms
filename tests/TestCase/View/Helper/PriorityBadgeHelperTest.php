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

use MeTools\TestSuite\HelperTestCase;

/**
 * PriorityBadgeHelperTest class
 * @property \MeCms\View\Helper\PriorityBadgeHelper $Helper
 */
class PriorityBadgeHelperTest extends HelperTestCase
{
    /**
     * @test
     * @uses \MeCms\View\Helper\PriorityBadgeHelper::badge()
     */
    public function testBadge(): void
    {
        $this->assertSame('<span class="badge priority-verylow" tooltip="Very low">1</span>', $this->Helper->badge(1));
        $this->assertSame('<span class="badge priority-low" tooltip="Low">2</span>', $this->Helper->badge(2));
        $this->assertSame('<span class="badge priority-normal" tooltip="Normal">3</span>', $this->Helper->badge(3));
        $this->assertSame('<span class="badge priority-high" tooltip="High">4</span>', $this->Helper->badge(4));
        $this->assertSame('<span class="badge priority-veryhigh" tooltip="Very high">5</span>', $this->Helper->badge(5));

        $this->assertSame('<span class="badge priority-normal" tooltip="Normal">3</span>', $this->Helper->badge(7));
    }
}
