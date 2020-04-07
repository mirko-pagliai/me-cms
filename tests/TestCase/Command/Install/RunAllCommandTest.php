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
use MeCms\TestSuite\TestCase;
use MeTools\Console\Command;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * RunAllCommandTest class
 */
class RunAllCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * @var array
     */
    protected $debug = [];

    /**
     * Tests for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $io = $this->getMockBuilder(ConsoleIo::class)
            ->setMethods(['askChoice'])
            ->setConstructorArgs([new ConsoleOutput(), new ConsoleOutput()])
            ->getMock();

        $io->method('askChoice')->will($this->returnValue('y'));

        $this->Command->questions = array_map(function ($question) {
            $command = $this->getMockBuilder(Command::class)
                ->setMethods(['execute'])
                ->getMock();
            $command->method('execute')->will($this->returnCallback(function () use ($question) {
                //This also tests the class exists and is instantiable
                $this->debug[] = get_class(new $question['command']);
            }));
            $question['command'] = $command;

            return $question;
        }, $this->Command->questions);

        $expected = [
            'MeTools\Command\Install\CreateDirectoriesCommand',
            'MeTools\Command\Install\SetPermissionsCommand',
            'MeTools\Command\Install\CreateRobotsCommand',
            'MeTools\Command\Install\FixComposerJsonCommand',
            'MeTools\Command\Install\CreatePluginsLinksCommand',
            'MeTools\Command\Install\CreateVendorsLinksCommand',
            'MeCms\Command\Install\CopyConfigCommand',
            'MeCms\Command\Install\FixElFinderCommand',
            'MeCms\Command\VersionUpdatesCommand',
            'MeCms\Command\Install\CreateGroupsCommand',
            'MeCms\Command\Install\CreateAdminCommand',
        ];
        $this->assertNull($this->Command->run([], $io));
        $this->assertEquals($expected, $this->debug);
    }
}
