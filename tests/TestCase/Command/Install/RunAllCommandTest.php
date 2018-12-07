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
namespace MeCms\Test\TestCase\Command\Install;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use MeCms\TestSuite\ConsoleIntegrationTestCase;
use MeTools\Console\Command;

/**
 * RunAllCommandTest class
 */
class RunAllCommandTest extends ConsoleIntegrationTestCase
{
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
            ->getMock();

        $io->method('askChoice')->will($this->returnValue('y'));

        $this->Shell->questions = array_map(function ($question) {
            $command = $this->getMockForShell(Command::class, ['execute']);
            $command->method('execute')->will($this->returnCallback(function () use ($question) {
                $this->debug[] = $question['command'];
            }));
            $question['command'] = $command;

            return $question;
        }, $this->Shell->questions);

        $expected = [
            'MeTools\Command\Install\SetPermissionsCommand',
            'MeTools\Command\Install\CreateRobotsCommand',
            'MeTools\Command\Install\FixComposerJsonCommand',
            'MeTools\Command\Install\CreatePluginsLinksCommand',
            'MeTools\Command\Install\CreateVendorsLinksCommand',
            'MeCms\Command\Install\CopyConfigCommand',
            'MeCms\Command\Install\FixKcfinderCommand',
        ];
        $this->Shell->execute(new Arguments([], ['force' => true], []), $io);
        $this->assertEquals($expected, $this->debug);

        $expected = array_merge(
            ['MeTools\Command\Install\CreateDirectoriesCommand'],
            $expected,
            ['MeCms\Command\Install\CreateGroupsCommand', 'MeCms\Command\Install\CreateAdminCommand']
        );
        $this->debug = [];
        $this->Shell->execute(new Arguments([], [], []), $io);
        $this->assertEquals($expected, $this->debug);
    }
}
