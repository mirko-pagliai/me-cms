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
namespace MeCms\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;
use MeCms\Shell\UserShell;

/**
 * InstallShellTest class
 */
class UserShellTest extends TestCase
{
    /**
     * @var \MeCms\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * @var \MeCms\Shell\UserShell
     */
    protected $UserShell;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.users',
        'plugin.me_cms.users_groups',
    ];

    /**
     * Internal method.
     * It sets some expectations for shell on `in()` method.
     * @return void
     */
    protected function _setShellExpectsForInMethod()
    {
        $this->UserShell->expects($this->at(0))
            ->method('in')
            ->will($this->returnValue('myusername'));

        $this->UserShell->expects($this->at(1))
            ->method('in')
            ->will($this->returnValue('password1/'));

        $this->UserShell->expects($this->at(2))
            ->method('in')
            ->will($this->returnValue('password1/'));

        $this->UserShell->expects($this->at(3))
            ->method('in')
            ->will($this->returnValue('myemail@example.com'));

        $this->UserShell->expects($this->at(4))
            ->method('in')
            ->will($this->returnValue('Alfa'));

        $this->UserShell->expects($this->at(5))
            ->method('in')
            ->will($this->returnValue('Beta'));
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Users = TableRegistry::get('MeCms.Users');

        $this->out = new ConsoleOutput();
        $this->err = new ConsoleOutput();
        $this->io = new ConsoleIo($this->out, $this->err);
        $this->io->level(2);

        $this->UserShell = $this->getMockBuilder(UserShell::class)
            ->setMethods(['in', '_stop'])
            ->setConstructorArgs([$this->io])
            ->getMock();

        $this->UserShell->initialize();
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        unset($this->Users, $this->InstallShell, $this->io, $this->err, $this->out);
    }

    /**
     * Test for `add()` method
     * @test
     */
    public function testAdd()
    {
        $this->_setShellExpectsForInMethod();

        $this->UserShell->expects($this->at(6))
            ->method('in')
            ->will($this->returnValue(3));

        $id = $this->UserShell->add();
        $this->assertNotEmpty($id);

        $this->assertEquals([
            '+----+---------+',
            '| <info>ID</info> | <info>Name</info>    |',
            '+----+---------+',
            '| 1  | Admin   |',
            '| 2  | Manager |',
            '| 3  | User    |',
            '+----+---------+',
            '<success>The user has been saved</success>',
        ], $this->out->messages());
        $this->assertEmpty($this->err->messages());

        $user = $this->Users->find()->where(['id' => $id])->first();

        $this->assertNotEmpty($user);
        $this->assertEquals(3, $user->group_id);
    }

    /**
     * Test for `add()` method, with invalid values
     * @test
     */
    public function testAddInvalidValues()
    {
        $this->UserShell->expects($this->any())
            ->method('in')
            ->will($this->returnValue('test'));

        $this->UserShell->params['group'] = 2;
        $this->UserShell->params['verbose'] = true;
        $this->assertFalse($this->UserShell->add());

        $this->assertEmpty($this->out->messages());
        $this->assertEquals([
            '<error>An error occurred, try again</error>',
            '<error>The user could not be saved</error>',
            '<error>Field `email`: you have to enter a valid value</error>',
            '<error>Field `first_name`: allowed chars: letters, apostrophe, space. Has to begin with a capital letter</error>',
            '<error>Field `last_name`: allowed chars: letters, apostrophe, space. Has to begin with a capital letter</error>',
            '<error>Field `password`: must be at least 8 chars</error>',
            '<error>Field `password`: the password should contain letters, numbers and symbols</error>',
        ], $this->err->messages());
    }

    /**
     * Test for `add()` method, with no users groups
     * @test
     */
    public function testAddNoUsersGroups()
    {
        //Deletes all groups
        $this->assertNotEquals(0, $this->Users->Groups->deleteAll(['id >=' => '1']));

        $this->assertFalse($this->UserShell->add());

        $this->assertEmpty($this->out->messages());
        $this->assertEquals([
            '<error>Before you can manage users, you have to create at least a user group</error>',
        ], $this->err->messages());
    }

    /**
     * Test for `add()` method, using the `group` param
     * @test
     */
    public function testAddUsingGroupParam()
    {
        $this->_setShellExpectsForInMethod();

        $this->UserShell->params['group'] = 2;
        $id = $this->UserShell->add();
        $this->assertNotEmpty($id);

        $this->assertEquals(['<success>The user has been saved</success>'], $this->out->messages());
        $this->assertEmpty($this->err->messages());

        $user = $this->Users->find()->where(['id' => $id])->first();

        $this->assertNotEmpty($user);
        $this->assertEquals(2, $user->group_id);
    }

    /**
     * Test for `add()` method, with empty values
     * @test
     */
    public function testAddWithEmptyValues()
    {
        $this->UserShell->params['group'] = 2;
        $this->assertFalse($this->UserShell->add());

        $this->assertEmpty($this->out->messages());
        $this->assertEquals([
            '<error>Field `username` is empty. Try again</error>',
        ], $this->err->messages());
    }

    /**
     * Test for `add()` method, with a no existing group
     * @test
     */
    public function testAddWithNoExistingGroup()
    {
        $this->UserShell->expects($this->any())
            ->method('in')
            ->will($this->returnValue('test'));

        $this->UserShell->params['group'] = 4;
        $this->assertFalse($this->UserShell->add());

        $this->assertEmpty($this->out->messages());
        $this->assertEquals([
            '<error>Invalid group ID</error>',
        ], $this->err->messages());
    }

    /**
     * Test for `groups()` method
     * @test
     */
    public function testGroups()
    {
        $this->UserShell->groups();

        //Deletes all groups
        $this->assertNotEquals(0, $this->Users->Groups->deleteAll(['id >=' => '1']));

        $this->UserShell->groups();

        $this->assertEquals([
            '+----+---------+---------+-------+',
            '| <info>ID</info> | <info>Name</info>    | <info>Label</info>   | <info>Users</info> |',
            '+----+---------+---------+-------+',
            '| 1  | admin   | Admin   | 0     |',
            '| 2  | manager | Manager | 0     |',
            '| 3  | user    | User    | 0     |',
            '+----+---------+---------+-------+',
        ], $this->out->messages());
        $this->assertEquals(['<error>There are no user groups</error>'], $this->err->messages());
    }

    /**
     * Test for `users()` method
     * @test
     */
    public function testUsers()
    {
        $this->UserShell->users();

        $users = TableRegistry::get('MeCms.Users');

        //Deletes all users
        $this->assertNotEquals(0, $users->deleteAll(['id >=' => '1']));

        $this->UserShell->users();

        $this->assertTextEquals([
            '+----+----------+---------+--------------+------------------+-------+---------+------------------+',
            '| <info>ID</info> | <info>Username</info> | <info>Group</info>   | <info>Name</info>         | <info>Email</info>            | <info>Posts</info> | <info>Status</info>  | <info>Date</info>             |',
            '+----+----------+---------+--------------+------------------+-------+---------+------------------+',
            '| 1  | alfa     | Admin   | Alfa Beta    | alfa@test.com    | 2     | Active  | 2016/12/24 17:00 |',
            '| 2  | gamma    | Manager | Gamma Delta  | gamma@test.com   | 0     | Pending | 2016/12/24 17:01 |',
            '| 3  | ypsilon  | User    | Ypsilon Zeta | ypsilon@?est.com | 0     | Banned  | 2016/12/24 17:02 |',
            '| 4  | abc      | User    | Abc Def      | abc@example.com  | 1     | Active  | 2016/12/24 17:03 |',
            '+----+----------+---------+--------------+------------------+-------+---------+------------------+',
        ], $this->out->messages());
        $this->assertEquals(['<error>There are no users</error>'], $this->err->messages());
    }

    /**
     * Test for `getOptionParser()` method
     * @test
     */
    public function testGetOptionParser()
    {
        $parser = $this->UserShell->getOptionParser();

        $this->assertEquals('Cake\Console\ConsoleOptionParser', get_class($parser));
        $this->assertEquals([
            'add',
            'groups',
            'users',
        ], array_keys($parser->subcommands()));
        $this->assertEquals('Shell to handle users and user groups', $parser->description());
        $this->assertEquals(['help', 'quiet', 'verbose'], array_keys($parser->options()));
    }
}
