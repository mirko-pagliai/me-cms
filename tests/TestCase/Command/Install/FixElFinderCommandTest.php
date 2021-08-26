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

use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;
use MeCms\Command\Install\FixElFinderCommand;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;
use Tools\Exception\NotReadableException;

/**
 * FixElFinderCommandTest class
 */
class FixElFinderCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var string
     */
    protected $command = 'me_cms.fix_el_finder -v';

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $expectedFiles = [
            ELFINDER . 'php' . DS . 'connector.minimal.php',
            ELFINDER . 'elfinder-cke.html',
        ];
        @array_map('unlink', $expectedFiles);
        $this->exec($this->command);
        $this->assertExitWithSuccess();
        foreach ($expectedFiles as $expectedFile) {
            $this->assertOutputContains('Creating file ' . $expectedFile);
            $this->assertOutputContains('<success>Wrote</success> `' . $expectedFile . '`');
        }
        $this->assertErrorEmpty();

        $this->assertStringContainsString('\'path\' => \'' . UPLOADED . '\'', file_get_contents($expectedFiles[0]) ?: '');
        $this->assertStringContainsString('getFileCallback', file_get_contents($expectedFiles[1]) ?: '');
    }

    /**
     * Test for `execute()` method, file already exists
     * @test
     */
    public function testExecuteFileAlreadyExists(): void
    {
        $this->exec($this->command);
        $this->assertExitWithSuccess();
        $this->assertOutputRegExp('/already exists$/');
    }

    /**
     * Test for `execute()` method, not readable file
     * @test
     */
    public function testExecuteNotReadableFile(): void
    {
        $Command = $this->getMockBuilder(FixElFinderCommand::class)
            ->setMethods(['createElfinderCke'])
            ->getMock();

        $Command->method('createElfinderCke')->will($this->throwException(new NotReadableException()));

        $this->_err = new ConsoleOutput();
        $this->assertSame(0, $Command->run([], new ConsoleIo(new ConsoleOutput(), $this->_err)));
        $this->assertErrorContains('Filename is not readable');
    }
}
