<?php

/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Test\TestCase\Console;

use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;
use MeCms\Console\BaseUpdateShell;
use MeCms\Shell\UpdateShell;
use Reflection\ReflectionTrait;

/**
 * BaseUpdateShellTest class
 */
class BaseUpdateShellTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var \MeCms\Console\BaseUpdateShell
     */
    protected $BaseUpdateShell;

    /**
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    protected $err;

    /**
     * @var \Cake\Console\ConsoleIo
     */
    protected $io;

    /**
     * @var \Cake\TestSuite\Stub\ConsoleOutput
     */
    protected $out;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->BaseUpdateShell = new BaseUpdateShell;
        $this->UpdateShell = new UpdateShell;

        $this->out = new ConsoleOutput;
        $this->err = new ConsoleOutput;
        $this->io = new ConsoleIo($this->out, $this->err);
        $this->io->level(2);
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->BaseUpdateShell, $this->UpdateShell, $this->io, $this->err, $this->out);
    }

    /**
     * Test for `__construct()` method
     * @test
     */
    public function testConstruct()
    {
        $connection = $this->getProperty($this->BaseUpdateShell, 'connection');
        $this->assertInstanceOf('Cake\Database\Connection', $connection);

        $now = $this->getProperty($this->BaseUpdateShell, 'now');
        $this->assertInstanceOf('Cake\I18n\Time', $now);
    }

    /**
     * Test for `_checkColumn()` method
     * @test
     */
    public function testCheckColumn()
    {
        $this->assertTrue($this->invokeMethod($this->BaseUpdateShell, '_checkColumn', ['id', 'users']));
        $this->assertFalse($this->invokeMethod($this->BaseUpdateShell, '_checkColumn', ['noExistingColumn', 'users']));
    }

    /**
     * Test for `_getAllUpdateMethods()` method
     * @test
     */
    public function testGetAllUpdateMethods()
    {
        $methods = $this->invokeMethod($this->UpdateShell, '_getAllUpdateMethods');

        foreach ($methods as $method) {
            $this->assertRegExp('/^to[0-9]+v[0-9]+v.+$/', $method['name']);
            $this->assertRegExp('/^[0-9]+\.[0-9]+\..+$/', $method['version']);

            $this->assertTrue(method_exists($this->UpdateShell, $method['name']));

            preg_match('/^to([0-9]+)v([0-9]+)v(.+)$/', $method['name'], $matches);

            $this->assertEquals($method['version'], $matches[1] . '.' . $matches[2] . '.' . $matches[3]);
        }
    }

    /**
     * Test for `_getColumns()` method
     * @test
     */
    public function testGetColumns()
    {
        $this->assertEquals([
            'id',
            'group_id',
            'username',
            'email',
            'password',
            'first_name',
            'last_name',
            'active',
            'banned',
            'post_count',
            'created',
            'modified',
        ], $this->invokeMethod($this->BaseUpdateShell, '_getColumns', ['users']));
    }

    /**
     * Test for `_getColumns()` method, with a no existing table
     * @expectedException PDOException
     * @expectedExceptionMessage SQLSTATE[42S02]: Base table or view not found: 1146 Table 'test.noExistingTable' doesn't exist
     */
    public function testGetColumnsNoExistingTable()
    {
        $this->invokeMethod($this->BaseUpdateShell, '_getColumns', ['noExistingTable']);
    }

    /**
     * Test for `_getLatestUpdateMethod()` method
     * @test
     */
    public function testGetLatest()
    {
        $latest = $this->invokeMethod($this->UpdateShell, '_getLatestUpdateMethod');
        $methods = $this->invokeMethod($this->UpdateShell, '_getAllUpdateMethods');

        $this->assertEquals($latest, $methods[0]);
    }

    /**
     * Test for `_tableExists()` method
     * @test
     */
    public function testTableExists()
    {
        $this->assertTrue($this->invokeMethod($this->BaseUpdateShell, '_tableExists', ['users']));
        $this->assertFalse($this->invokeMethod($this->BaseUpdateShell, '_tableExists', ['noExisting']));
    }

    /**
     * Test for `_tables()` method
     * @test
     */
    public function testTables()
    {
        $this->assertEquals([
            'users',
            'users_groups',
        ], $this->invokeMethod($this->BaseUpdateShell, '_tables'));
    }

    /**
     * Test for `all()` method
     * @test
     */
    public function testAll()
    {
        $methods = $this->invokeMethod($this->UpdateShell, '_getAllUpdateMethods');

        //Sets methods to stub and the expected out messages
        foreach (array_reverse($methods) as $method) {
            $methodsToStub[] = $method['name'];
            $expectedOut[] = sprintf('Upgrading to %s', $method['version']);
            $expectedOut[] = sprintf('called `%s`', $method['name']);
        }

        //Mocks
        $this->UpdateShell = $this->getMockBuilder(UpdateShell::class)
            ->setMethods(am(['in', '_stop', '_getAllUpdateMethods'], $methodsToStub))
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->UpdateShell->method('_getAllUpdateMethods')
            ->will($this->returnValue($methods));

        foreach ($methodsToStub as $method) {
            $this->UpdateShell->method($method)
                ->will($this->returnCallback(function () use ($method) {
                    $this->out->write(sprintf('called `%s`', $method));
                }));
        }

        $this->UpdateShell->all();

        $this->assertEquals($expectedOut, $this->out->messages());
        $this->assertEmpty($this->err->messages());
    }

    /**
     * Test for `latest()` method
     * @test
     */
    public function testLatest()
    {
        $latest = $this->invokeMethod($this->UpdateShell, '_getLatestUpdateMethod');

        //Mocks
        $this->UpdateShell = $this->getMockBuilder(UpdateShell::class)
            ->setMethods(['in', '_stop', '_getLatestUpdateMethod', $latest['name']])
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->UpdateShell->method('_getLatestUpdateMethod')
            ->will($this->returnValue($latest));

        $this->UpdateShell->method($latest['name'])
            ->will($this->returnCallback(function () use ($latest) {
                $this->out->write(sprintf('called `%s`', $latest['name']));
            }));

        $this->UpdateShell->latest();

        $this->assertEquals([
            sprintf('Upgrading to %s', $latest['version']),
            sprintf('called `%s`', $latest['name']),
        ], $this->out->messages());
        $this->assertEmpty($this->err->messages());
    }

    /**
     * Test for `getOptionParser()` method
     * @test
     */
    public function testGetOptionParser()
    {
        $parser = $this->UpdateShell->getOptionParser();

        $methods = $this->invokeMethod($this->UpdateShell, '_getAllUpdateMethods');

        $methods = array_map(function ($method) {
            return $method['name'];
        }, $methods);

        asort($methods);

        $methods = am(['all', 'latest'], $methods);

        $this->assertInstanceOf('Cake\Console\ConsoleOptionParser', $parser);
        $this->assertEquals($methods, array_keys($parser->subcommands()));
    }
}
