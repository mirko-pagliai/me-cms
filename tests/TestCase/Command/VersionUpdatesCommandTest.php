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

namespace MeCms\Test\TestCase\Command;

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use MeCms\Command\VersionUpdatesCommand;
use MeTools\TestSuite\CommandTestCase;

/**
 * VersionUpdatesCommandTest class
 * @property \MeCms\Command\VersionUpdatesCommand $Command
 */
class VersionUpdatesCommandTest extends CommandTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.Posts',
        'plugin.MeCms.Users',
        'plugin.MeCms.Tags',
    ];

    /**
     * @test
     * @uses \MeCms\Command\VersionUpdatesCommand::addLastLoginsField()
     */
    public function testAddLastLoginsField(): void
    {
        $Table = $this->getTable('MeCms.Users');
        $connection = $Table->getConnection();

        $this->skipIf($connection->getDriver() instanceof Sqlite);

        if ($Table->getSchema()->hasColumn('last_logins')) {
            $command = 'ALTER TABLE `' . $Table->getTable() . '` DROP `last_logins`';
            if ($connection->getDriver() instanceof Postgres) {
                $command = 'ALTER TABLE ' . $Table->getTable() . ' DROP COLUMN last_logins;';
            }
            $connection->execute($command);
        }

        $this->Command->addLastLoginsField();
        $this->assertTrue($this->getTable('MeCms.Users')->getSchema()->hasColumn('last_logins'));
    }

    /**
     * @test
     * @uses \MeCms\Command\VersionUpdatesCommand::addEnableCommentsField()
     */
    public function testAddEnableCommentsField(): void
    {
        $getTables = fn(): array => [$this->getTable('MeCms.Posts'), $this->getTable('MeCms.Pages')];

        [$Posts, $Pages] = $getTables();
        $this->skipIf($Posts->getConnection()->getDriver() instanceof Sqlite);

        foreach ([$Posts, $Pages] as $Table) {
            if ($Table->getSchema()->hasColumn('enable_comments')) {
                $connection = $Table->getConnection();
                $command = 'ALTER TABLE `' . $Table->getTable() . '` DROP `enable_comments`';
                if ($connection->getDriver() instanceof Postgres) {
                    $command = 'ALTER TABLE ' . $Table->getTable() . ' DROP COLUMN enable_comments;';
                }
                $connection->execute($command);
            }
        }

        $this->Command->addEnableCommentsField();
        foreach ($getTables() as $Table) {
            $this->assertTrue($Table->getSchema()->hasColumn('enable_comments'));
        }
    }

    /**
     * @test
     * @uses \MeCms\Command\VersionUpdatesCommand::alterTagColumnSize()
     */
    public function testAlterTagColumnSize(): void
    {
        $Table = $this->getTable('MeCms.Tags');
        $connection = $Table->getConnection();
        $this->skipIf($connection->getDriver() instanceof Sqlite);

        $command = 'ALTER TABLE ' . $Table->getTable() . ' MODIFY tag varchar(254) NOT NULL';
        if ($connection->getDriver() instanceof Postgres) {
            $command = 'ALTER TABLE ' . $Table->getTable() . ' ALTER COLUMN tag TYPE varchar(254);';
        }
        $connection->execute($command);
        $this->assertEquals(254, $Table->getSchema()->getColumn('tag')['length']);

        $this->Command->alterTagColumnSize();
        $this->assertEquals(255, $this->getTable('MeCms.Tags')->getSchema()->getColumn('tag')['length']);
    }

    /**
     * @test
     * @uses \MeCms\Command\VersionUpdatesCommand::deleteOldDirectories()
     */
    public function testDeleteOldDirectories(): void
    {
        $dirs = [WWW_ROOT . 'fonts', TMP . 'login'];
        array_map('mkdir', array_filter($dirs, 'is_writable'));
        $this->Command->deleteOldDirectories();
        array_walk($dirs, [$this, 'assertFileDoesNotExist']);
    }

    /**
     * @test
     * @uses \MeCms\Command\VersionUpdatesCommand::execute()
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.version_updates -h');
        $this->assertNotEmpty($this->_out->messages());

        $expectedMethods = get_child_methods(VersionUpdatesCommand::class);
        $Command = $this->createPartialMock(VersionUpdatesCommand::class, $expectedMethods);
        foreach ($expectedMethods as $method) {
            $Command->expects($this->once())->method($method);
        }

        $this->assertNull($Command->run([], new ConsoleIo(new StubConsoleOutput(), new StubConsoleOutput())));
    }
}
