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

use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * FixElFinderCommandTest class
 */
class FixElFinderCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var string
     */
    protected $command = 'me_cms.fix_elfinder -v';

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $expectedFile = ELFINDER . 'php' . DS . 'connector.minimal.php';
        @unlink($expectedFile);
        $this->exec($this->command);
        $this->assertExitWithSuccess();
        $this->assertOutputContains('Creating file ' . $expectedFile);
        $this->assertOutputContains('<success>Wrote</success> `' . $expectedFile . '`');
        $this->assertErrorEmpty();
    }
}
