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

namespace MeCms\Test\TestCase\Command;

use Cake\Console\ConsoleIo;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\TestSuite\Stub\ConsoleOutput;
use MeCms\Command\VersionUpdatesCommand;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * VersionUpdatesCommandTest class
 * @property \MeCms\Command\VersionUpdatesCommand $Command
 */
class VersionUpdatesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var bool
     */
    public $autoFixtures = false;

    /**
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Pages',
        'plugin.MeCms.Posts',
        'plugin.MeCms.Tags',
    ];

    /**
     * Test for `addEnableCommentsField()` method
     * @test
     */
    public function testAddEnableCommentsField(): void
    {
        $getTables = function () {
            return [$this->getTable('MeCms.Posts'), $this->getTable('MeCms.Pages')];
        };

        [$Posts, $Pages] = $getTables();
        $this->skipIf($Posts->getConnection()->getDriver() instanceof Sqlite);
        $this->loadFixtures('Pages', 'Posts');

        foreach ([$Posts, $Pages] as $Table) {
            $connection = $Table->getConnection();
            $command = 'ALTER TABLE `' . $Table->getTable() . '` DROP `enable_comments`';
            if ($connection->getDriver() instanceof Postgres) {
                $command = 'ALTER TABLE ' . $Table->getTable() . ' DROP COLUMN enable_comments;';
            }
            $connection->execute($command);
        }

        $this->Command->addEnableCommentsField();
        foreach ($getTables() as $Table) {
            $this->assertTrue($Table->getSchema()->hasColumn('enable_comments'));
        }
    }

    /**
     * Test for `alterTagColumnSize()` method
     * @test
     */
    public function testAlterTagColumnSize(): void
    {
        $Table = $this->getTable('MeCms.Tags');
        $connection = $Table->getConnection();
        $this->skipIf($connection->getDriver() instanceof Sqlite);
        $this->loadFixtures('Tags');

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
     * Test for `deleteOldDirectories()` method
     * @test
     */
    public function testDeleteOldDirectories(): void
    {
        $dir = WWW_ROOT . 'fonts';
        @mkdir($dir);
        $this->assertFileExists($dir);
        $this->Command->deleteOldDirectories();
        $this->assertFileDoesNotExist($dir);
    }

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.version_updates -h');
        $this->assertNotEmpty($this->_out->messages());

        $methods = get_child_methods(VersionUpdatesCommand::class);
        $Command = $this->getMockBuilder(VersionUpdatesCommand::class)
            ->setMethods($methods)
            ->getMock();

        foreach ($methods as $method) {
            $Command->expects($this->once())->method($method);
        }

        $this->assertNull($Command->run([], new ConsoleIo(new ConsoleOutput(), new ConsoleOutput())));
    }
}
