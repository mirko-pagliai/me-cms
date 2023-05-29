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
    /**
     * @uses \MeCms\Command\Install\FixElFinderCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $Filesystem = new Filesystem();
        $Filesystem->mkdir(WWW_VENDOR);
        $command = 'me_cms.fix_el_finder -v';
        $connector = ELFINDER . 'php' . DS . 'connector.minimal.php';
        $elfinderCke = ELFINDER . 'elfinder-cke.html';

        /**
         * Runs and creates files
         */
        $this->exec($command);

        $this->assertFileExists($connector);
        $connectorContent = file_get_contents($connector) ?: '';
        $this->assertStringContainsString('require_once \'' . APP . 'vendor' . DS . 'autoload.php' . '\';', $connectorContent);
        $this->assertStringContainsString('\'path\' => \'' . UPLOADED . '\'', $connectorContent);
        $this->assertStringContainsString('\'URL\' => \'http://localhost/files\'', $connectorContent);

        $this->assertFileExists($elfinderCke);
        $this->assertStringContainsString('getFileCallback', file_get_contents($elfinderCke) ?: '');

        $this->assertExitSuccess();
        $this->assertOutputContains('Creating file ' . $connector);
        $this->assertOutputContains('<success>Wrote</success> `' . $connector . '`');
        $this->assertOutputContains('Creating file ' . $elfinderCke);
        $this->assertOutputContains('<success>Wrote</success> `' . $elfinderCke . '`');
        $this->assertErrorEmpty();

        /**
         * Runs again. Files already exist
         */
        $this->exec($command);
        $this->assertExitSuccess();
        $this->assertOutputContains('File or directory `' . rtr($connector) . '` already exists');
        $this->assertOutputContains('File or directory `' . rtr($elfinderCke) . '` already exists');
        $this->assertErrorEmpty();

        /**
         * Runs, with an exception
         */
        $Command = $this->createPartialMock(FixElFinderCommand::class, ['createElfinderCke']);
        $Command->method('createElfinderCke')->willThrowException(new ErrorException('Exception message'));
        $this->_err = new StubConsoleOutput();
        $this->_exitCode = $Command->run([], new ConsoleIo(null, $this->_err));
        $this->assertExitError();
        $this->assertErrorContains('Exception message');

        $Filesystem->remove(WWW_VENDOR);
    }
}
