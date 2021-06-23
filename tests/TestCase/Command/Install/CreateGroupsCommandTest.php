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

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Database\Connection;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\ORM\Query;
use Cake\ORM\Table;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * CreateGroupsCommandTest class
 * @property \MeCms\Command\Install\CreateGroupsCommand $Command
 */
class CreateGroupsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var bool
     */
    public $autoInitializeClass = true;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        //A group already exists
        $this->exec('me_cms.create_groups -v');
        $this->assertExitWithSuccess();
        $this->assertOutputEmpty();
        $this->assertErrorContains('Some user groups already exist');

        //With no user groups
        $UsersGroups = $this->getTable('MeCms.UsersGroups');
        $UsersGroups->deleteAll(['id is NOT' => null]);
        $this->_in = $this->_err = null;
        $this->exec('me_cms.create_groups -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('The user groups have been created');
        $this->assertErrorEmpty();

        //Checks the user groups exist
        $this->assertEquals([1, 2, 3], $UsersGroups->find()->extract('id')->toList());
    }

    /**
     * Provider for `testExecuteOtherDrivers()`
     * @return array
     */
    public function driverProvider(): array
    {
        return [
            'postgres' => [Postgres::class],
            'sqlite' => [Sqlite::class],
        ];
    }

    /**
     * Test for `execute()` method
     * @param class-string<\Cake\Database\DriverInterface> $driver
     * @dataProvider driverProvider
     * @test
     */
    public function testExecuteOtherDrivers($driver): void
    {
        $this->skipIf(IS_WIN);

        /** @var \MeCms\Model\Table\UsersGroupsTable&\PHPUnit\Framework\MockObject\MockObject $UsersGroups */
        $UsersGroups = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $UsersGroups->method('find')->will($this->returnCallback(function () {
            $query = $this->getMockBuilder(Query::class)
                ->disableOriginalConstructor()
                ->setMethods(array_merge(get_class_methods(Query::class), ['isEmpty']))
                ->getMock();
            $query->method('isEmpty')->will($this->returnValue(true));

            return $query;
        }));
        $this->Command->UsersGroups = $UsersGroups;

        $driver = $this->getMockBuilder($driver)->getMock();
        $driver->method('enabled')->will($this->returnValue(true));
        $connection = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([compact('driver')])
            ->setMethods(['execute'])
            ->getMock();
        $this->Command->UsersGroups->method('getConnection')->will($this->returnValue($connection));

        $this->assertNull($this->Command->execute(new Arguments([], [], []), new ConsoleIo()));
    }
}
