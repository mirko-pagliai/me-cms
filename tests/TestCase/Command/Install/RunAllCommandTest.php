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

namespace MeCms\Test\TestCase\Command\Install;

use MeCms\Command\Install\RunAllCommand;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * RunAllCommandTest class
 */
class RunAllCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Tests for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $questions = (new RunAllCommand())->questions;
        $this->exec('me_cms.install -v', array_fill(0, count($questions), 'n'));

        $expectedQuestions = array_column($questions, 'question');
        $outputQuestions = array_map(function (string $output): string {
            return preg_match('/\<question\>([\w\s\.]+\?)\<\/question\>/', $output, $matches) ? $matches[1] : '';
        }, $this->_out->messages());
        $this->assertSame($expectedQuestions, $outputQuestions);
    }
}
