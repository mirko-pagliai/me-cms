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

namespace MeCms\Test\TestCase\Mailer;

use MeCms\Mailer\Mailer;
use MeCms\TestSuite\TestCase;

/**
 * MailerTest class
 */
class MailerTest extends TestCase
{
    /**
     * Tests for `__construct()` method
     * @uses \MeCms\Mailer\Mailer::__construct()
     * @test
     */
    public function testConstruct(): void
    {
        $Mailer = $this->getMockBuilder(Mailer::class)->getMockForAbstractClass();

        $this->assertContains('Html', array_keys($Mailer->viewBuilder()->getHelpers()));
        $this->assertEquals(['email@example.com' => 'MeCms'], $Mailer->getSender());
        $this->assertEquals(['email@example.com' => 'MeCms'], $Mailer->getFrom());
        $this->assertEquals('html', $Mailer->getEmailFormat());
        $this->assertEquals([], $Mailer->getRenderer()->viewBuilder()->getVars());
    }
}
