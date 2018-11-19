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

use Cake\Console\ConsoleOptionParser;
use MeCms\Model\Table\UsersTable;
use MeTools\TestSuite\ConsoleIntegrationTestCase;
use MeTools\TestSuite\Traits\MockTrait;

/**
 * InstallShellTest class
 */
class UserShellTest extends ConsoleIntegrationTestCase
{
    use MockTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $Table;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Users',
        'plugin.me_cms.UsersGroups',
    ];

    /**
     * Called before every test method
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Table = $this->getMockForTable(UsersTable::class, null);
    }

    /**
     * Test for `add()` method
     * @test
     */
    public function testAdd()
    {
        $example = ['myusername', 'password1/', 'password1/', 'mail@example.com', 'Alfa', 'Beta'];
        $expectedUserId = $this->Table->find()->extract('id')->last() + 1;

        $this->exec('me_cms.user add', array_merge($example, ['3']));
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<question>Group ID</question>');
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . $expectedUserId . '</success>');
        $this->assertErrorEmpty();

        //Checks the user has been created
        $this->assertEquals(3, $this->Table->findById($expectedUserId)->extract('group_id')->first());
        $this->Table->delete($this->Table->get($expectedUserId));

        //Tries using the `group` param
        $this->exec('me_cms.user add --group 2', $example);
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . ++$expectedUserId . '</success>');
        $this->assertErrorEmpty();

        //Checks the user has been created
        $this->assertEquals(2, $this->Table->findById($expectedUserId)->extract('group_id')->first());

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
        $this->Table->Groups->deleteAll(['id >=' => '1']);
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
        $expectedRows = $this->Table->Groups->find()->map(function ($row) {
            return [(string)$row->id, $row->name, $row->label, $row->user_count];
        });
        $this->exec('me_cms.user groups');
        $this->assertExitWithSuccess();
        $this->assertTableHeadersEquals(['ID', 'Name', 'Label', 'Users']);
        $this->assertTableRowsEquals($expectedRows->toList());

        //Deletes all groups
        $this->Table->Groups->deleteAll(['id >=' => '1']);
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
        $expectedRows = $this->Table->find()->contain('Groups')->map(function ($user) {
            if ($user->banned) {
                $user->status = __d('me_cms', 'Banned');
            } elseif (!$user->active) {
                $user->status = __d('me_cms', 'Pending');
            } else {
                $user->status = __d('me_cms', 'Active');
            }

            return [
                $user->id,
                $user->username,
                $user->group->label,
                $user->full_name,
                $user->email,
                $user->post_count,
                $user->status,
                $user->created->i18nFormat('yyyy/MM/dd HH:mm'),
            ];
        });
        $this->exec('me_cms.user users');
        $this->assertExitWithSuccess();
        $this->assertTableHeadersEquals(['ID', 'Username', 'Group', 'Name', 'Email', 'Posts', 'Status', 'Date']);
        $this->assertTableRowsEquals($expectedRows->toList());

        //Deletes all users
        $this->Table->deleteAll(['id >=' => '1']);
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
        $parser = $this->Shell->getOptionParser();
        $this->assertInstanceOf(ConsoleOptionParser::class, $parser);
        $this->assertEquals('Shell to handle users and user groups', $parser->getDescription());
        $this->assertArrayKeysEqual(['help', 'quiet', 'verbose'], $parser->options());
        $this->assertArrayKeysEqual(['add', 'groups', 'users'], $parser->subcommands());
    }
}
