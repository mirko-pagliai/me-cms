<?php
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
    public function testConstruct()
    {
        $mailer = new Mailer;

        $this->assertEquals([ME_TOOLS . '.Html'], $mailer->viewBuilder()->getHelpers());
        $this->assertEquals(['email@example.com' => ME_CMS], $mailer->getEmailInstance()->getSender());
        $this->assertEquals(['email@example.com' => ME_CMS], $mailer->getEmailInstance()->getFrom());
        $this->assertEquals('html', $mailer->getEmailInstance()->getEmailFormat());
        $this->assertEquals([], $mailer->getEmailInstance()->getViewVars());
    }
}
