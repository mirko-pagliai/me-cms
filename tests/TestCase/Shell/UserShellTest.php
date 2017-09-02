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
namespace MeCms\Test\TestCase\Shell;

use Cake\ORM\TableRegistry;
use MeCms\Shell\UserShell;
use MeTools\TestSuite\ConsoleIntegrationTestCase;

/**
 * InstallShellTest class
 */
class UserShellTest extends ConsoleIntegrationTestCase
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
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Users = TableRegistry::get(ME_CMS . '.Users');

        $this->UserShell = new UserShell;
    }

    /**
     * Test for `add()` method
     * @test
     */
    public function testAdd()
    {
        $example = ['myusername', 'password1/', 'password1/', 'mail@example.com', 'Alfa', 'Beta'];
        $id = 1 + $this->Users->find()->order(['id' => 'DESC'])->extract('id')->first();

        $this->exec('me_cms.user add', array_merge($example, ['3']));
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<question>Group ID</question>');
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . $id . '</success>');

        //Checks the user has been created
        $user = $this->Users->find()->where(compact('id'))->first();
        $this->assertNotEmpty($user);
        $this->assertEquals(3, $user->group_id);

        $this->Users->delete($this->Users->get($id));
        $id++;

        //Tries using the `group` param
        $this->exec('me_cms.user add --group 2', $example);
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . $id . '</success>');

        //Checks the user has been created
        $user = $this->Users->find()->where(compact('id'))->first();
        $this->assertNotEmpty($user);
        $this->assertEquals(2, $user->group_id);

        //Tries with a no existing group
        $this->exec('me_cms.user add --group 123', $example);
        $this->assertExitWithError();
        $this->assertErrorContains('<error>Invalid group ID</error>');

        //Tries with empty data
        $this->exec('me_cms.user add -v', []);
        $this->assertExitWithError();
        $this->assertErrorContains('<error>Field `username` is empty. Try again</error>');

        //Tries with wrong data
        $this->exec('me_cms.user add -v', ['ab', 'password', 'password2', 'mail', 'aa', 'bb', '3']);
        $this->assertExitWithError();
        $this->assertErrorContains('<error>The operation has not been performed correctly</error>');
        $this->assertErrorContains('<error>The user could not be saved</error>');
        $this->assertErrorContains('<error>Field `email`: you have to enter a valid value</error>');
        $this->assertErrorContains('<error>Field `first_name`: must be between 3 and 40 chars</error>');
        $this->assertErrorContains('<error>Field `first_name`: allowed chars: letters, apostrophe, ' .
            'space. Has to begin with a capital letter</error>');
        $this->assertErrorContains('<error>Field `last_name`: must be between 3 and 40 chars</error>');
        $this->assertErrorContains('<error>Field `last_name`: allowed chars: letters, apostrophe, ' .
            'space. Has to begin with a capital letter</error>');
        $this->assertErrorContains('<error>Field `username`: must be between 4 and 40 chars</error>');
        $this->assertErrorContains('<error>Field `username`: allowed chars: lowercase letters, numbers, dash</error>');
        $this->assertErrorContains('<error>Field `password`: the password should contain letters, ' .
            'numbers and symbols</error>');
        $this->assertErrorContains('<error>Field `password_repeat`: passwords don\'t match</error>');

        //Tries with no groups
        $this->assertNotEquals(0, $this->Users->Groups->deleteAll(['id >=' => '1']));
        $this->exec('me_cms.user add -v');
        $this->assertExitWithError();
        $this->assertErrorContains('<error>Before you can manage users, you have to create at least a user group</error>');
    }

    /**
     * Test for `groups()` method
     * @test
     */
    public function testGroups()
    {
        $this->exec('me_cms.user groups');
        $this->assertExitWithSuccess();
        $this->assertEquals([
            '+----+---------+---------+-------+',
            '| <info>ID</info> | <info>Name</info>    | <info>Label</info>   | <info>Users</info> |',
            '+----+---------+---------+-------+',
            '| 1  | admin   | Admin   | 2     |',
            '| 2  | manager | Manager | 0     |',
            '| 3  | user    | User    | 3     |',
            '| 4  | fans    | Fans    | 3     |',
            '| 5  | people  | People  | 0     |',
            '+----+---------+---------+-------+',
        ], $this->_out->messages());

        //Deletes all groups
        $this->assertNotEquals(0, $this->Users->Groups->deleteAll(['id >=' => '1']));

        $this->exec('me_cms.user groups');
        $this->assertExitWithError();
        $this->assertErrorContains('<error>There are no user groups</error>');
    }

    /**
     * Test for `users()` method
     * @test
     */
    public function testUsers()
    {
        $this->exec('me_cms.user users');
        $this->assertExitWithSuccess();
        $this->assertEquals([
            '+----+----------+-------+--------------+-------------------+-------+---------+------------------+',
            '| <info>ID</info> | <info>Username</info> | <info>Group</info> | <info>Name</info>         | <info>Email</info>             | <info>Posts</info> | <info>Status</info>  | <info>Date</info>             |',
            '+----+----------+-------+--------------+-------------------+-------+---------+------------------+',
            '| 1  | alfa     | Admin | Alfa Beta    | alfa@test.com     | 2     | Active  | 2016/12/24 17:00 |',
            '| 2  | gamma    | User  | Gamma Delta  | gamma@test.com    | 0     | Pending | 2016/12/24 17:01 |',
            '| 3  | ypsilon  | User  | Ypsilon Zeta | ypsilon@test.com  | 0     | Banned  | 2016/12/24 17:02 |',
            '| 4  | abc      | User  | Abc Def      | abc@example.com   | 1     | Active  | 2016/12/24 17:03 |',
            '| 5  | delta    | Admin | Mno Pqr      | delta@example.com | 0     | Active  | 2016/12/24 17:04 |',
            '+----+----------+-------+--------------+-------------------+-------+---------+------------------+',
        ], $this->_out->messages());

        //Deletes all users
        $this->assertNotEquals(0, $this->Users->deleteAll(['id >=' => '1']));

        $this->exec('me_cms.user users');
        $this->assertExitWithError();
        $this->assertErrorContains('<error>There are no users</error>');
    }

    /**
     * Test for `getOptionParser()` method
     * @test
     */
    public function testGetOptionParser()
    {
        $parser = $this->UserShell->getOptionParser();

        $this->assertInstanceOf('Cake\Console\ConsoleOptionParser', $parser);
        $this->assertArrayKeysEqual([
            'add',
            'groups',
            'users',
        ], $parser->subcommands());
        $this->assertEquals('Shell to handle users and user groups', $parser->getDescription());
        $this->assertEquals(['help', 'quiet', 'verbose'], array_keys($parser->options()));
    }
}
