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
     * @test
     */
    public function testConstruct(): void
    {
        $mailer = $this->getMockBuilder(Mailer::class)
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->assertEquals(['MeTools.Html'], $mailer->viewBuilder()->getHelpers());
        $this->assertEquals(['email@example.com' => 'MeCms'], $mailer->getSender());
        $this->assertEquals(['email@example.com' => 'MeCms'], $mailer->getFrom());
        $this->assertEquals('html', $mailer->getEmailFormat());
        $this->assertEquals([], $mailer->getRenderer()->viewBuilder()->getVars());
    }
}
