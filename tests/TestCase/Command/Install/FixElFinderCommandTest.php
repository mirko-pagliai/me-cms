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
use Cake\Routing\Router;
use ErrorException;
use MeCms\Command\Install\FixElFinderCommand;
use MeTools\TestSuite\CommandTestCase;
use Tools\Filesystem;

/**
 * FixElFinderCommandTest class
 */
class FixElFinderCommandTest extends CommandTestCase
{
    /**
     * @uses \MeCms\Command\Install\FixElFinderCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $command = 'me_cms.fix_el_finder -v';

        $connector = Filesystem::concatenate(ELFINDER, 'php', 'connector.minimal.php');
        $elfinderCke = Filesystem::concatenate(ELFINDER, 'elfinder-cke.html');
        array_map('unlink', array_filter([$connector, $elfinderCke], 'file_exists'));

        $this->exec($command);
        $this->assertExitSuccess();
        $this->assertErrorEmpty();

        $this->assertOutputContains('Creating file ' . $connector);
        $this->assertOutputContains('<success>Wrote</success> `' . $connector . '`');
        $connectorContent = file_get_contents($connector) ?: '';
        $this->assertStringContainsString('require_once \'' . Filesystem::concatenate(APP, 'vendor', 'autoload.php') . '\';', $connectorContent);
        $this->assertStringContainsString('\'path\' => \'' . UPLOADED . '\'', $connectorContent);
        $this->assertStringContainsString('\'URL\' => \'' . Router::url('/files', true) . '\'', $connectorContent);

        $this->assertOutputContains('Creating file ' . $elfinderCke);
        $this->assertOutputContains('<success>Wrote</success> `' . $elfinderCke . '`');
        $this->assertStringContainsString('getFileCallback', file_get_contents($elfinderCke) ?: '');

        //File already exists
        $this->exec($command);
        $this->assertExitSuccess();
        $this->assertOutputContains('File or directory `' . rtr($connector) . '` already exists');
        $this->assertOutputContains('File or directory `' . rtr($elfinderCke) . '` already exists');

        //With an exception
        $Command = $this->createPartialMock(FixElFinderCommand::class, ['createElfinderCke']);
        $Command->method('createElfinderCke')->willThrowException(new ErrorException('Exception message'));
        $this->_err = new StubConsoleOutput();
        $this->assertSame(0, $Command->run([], new ConsoleIo(new StubConsoleOutput(), $this->_err)));
        $this->assertErrorContains('Exception message');
    }
}
