<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use ErrorException;
use MeCms\Command\Install\FixElFinderCommand;
use MeTools\TestSuite\CommandTestCase;
use Tools\Filesystem;

/**
 * FixElFinderCommandTest class
 */
class FixElFinderCommandTest extends CommandTestCase
{
    protected const EXPECTED_FILES = [
        ELFINDER . 'php' . DS . 'connector.minimal.php',
        ELFINDER . 'elfinder-cke.html',
    ];

    /**
     * @var string
     */
    protected string $command = 'me_cms.fix_el_finder -v';

    /**
     * @uses \MeCms\Command\Install\FixElFinderCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        array_map('unlink', array_filter(self::EXPECTED_FILES, 'is_writable'));
        $this->exec($this->command);
        $this->assertExitSuccess();
        foreach (self::EXPECTED_FILES as $expectedFile) {
            $this->assertOutputContains('Creating file ' . $expectedFile);
            $this->assertOutputContains('<success>Wrote</success> `' . $expectedFile . '`');
        }
        $this->assertErrorEmpty();

        $this->assertStringContainsString('\'path\' => \'' . UPLOADED . '\'', file_get_contents(self::EXPECTED_FILES[0]) ?: '');
        $this->assertStringContainsString('getFileCallback', file_get_contents(self::EXPECTED_FILES[1]) ?: '');

        //File already exists
        $this->exec($this->command);
        $this->assertExitSuccess();
        foreach (self::EXPECTED_FILES as $expectedFile) {
            $this->assertOutputContains('File or directory `' . Filesystem::instance()->rtr($expectedFile) . '` already exists');
        }

        //With an exception
        $Command = $this->createPartialMock(FixElFinderCommand::class, ['createElfinderCke']);
        $Command->method('createElfinderCke')->willThrowException(new ErrorException('Exception message'));
        $this->_err = new StubConsoleOutput();
        $this->assertSame(0, $Command->run([], new ConsoleIo(new StubConsoleOutput(), $this->_err)));
        $this->assertErrorContains('Exception message');
    }
}
