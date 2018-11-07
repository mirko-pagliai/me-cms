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
        'plugin.me_cms.Users',
        'plugin.me_cms.UsersGroups',
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
        $id = $this->Users->find()->extract('id')->last();

        $this->exec('me_cms.user add', array_merge($example, ['3']));
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<question>Group ID</question>');
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . ++$id . '</success>');

        //Checks the user has been created
        $this->assertEquals(3, $this->Users->findById($id)->extract('group_id')->first());

        $this->Users->delete($this->Users->get($id));

        //Tries using the `group` param
        $this->exec('me_cms.user add --group 2', $example);
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . ++$id . '</success>');

        //Checks the user has been created
        $this->assertEquals(2, $this->Users->findById($id)->extract('group_id')->first());

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
        $this->Users->Groups->deleteAll(['id >=' => '1']);
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

        $messages = $this->_out->messages();
        $this->assertRegExp('/^[\+\-]+$/', current($messages));
        $headers = preg_replace('/\s*\<info\>(\w+)\<\/info\>\s*/', '${1}', array_filter(explode('|', next($messages))));
        $this->assertEquals(['ID', 'Name', 'Label', 'Users'], array_values($headers));
        $this->assertRegExp('/^[\+\-]+$/', end($messages));

        //Removes the already checked lines
        $lastLine = count($messages) - 1;
        unset($messages[0], $messages[1], $messages[2], $messages[$lastLine]);

        foreach ($messages as $line) {
            $this->assertRegExp('/^|\s*\d+\s*|\s*\w+\s*|\s*\d+\s*|$/', $line);
        }

        //Deletes all groups
        $this->Users->Groups->deleteAll(['id >=' => '1']);

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

        $messages = $this->_out->messages();
        $this->assertRegExp('/^[\+\-]+$/', current($messages));
        $headers = preg_replace('/\s*\<info\>(\w+)\<\/info\>\s*/', '${1}', array_filter(explode('|', next($messages))));
        $this->assertEquals(['ID', 'Username', 'Group', 'Name', 'Email', 'Posts', 'Status', 'Date'], array_values($headers));
        $this->assertRegExp('/^[\+\-]+$/', next($messages));
        $this->assertRegExp('/^[\+\-]+$/', end($messages));

        //Removes the already checked lines
        $lastLine = count($messages) - 1;
        unset($messages[0], $messages[1], $messages[2], $messages[$lastLine]);

        foreach ($messages as $line) {
            $this->assertRegExp('/^\|\s*\d+\s*\|\s*\w+\s*\|\s*\w+\s*\|\s*\w+\s\w+\s*\|\s*\S+\s*\|\s*\d+\s*\|\s*\w+\s*\|\s*\S+\s\S+\s*\|$/', $line);
        }

        //Deletes all users
        $this->Users->deleteAll(['id >=' => '1']);

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
        $this->assertArrayKeysEqual(['add', 'groups', 'users'], $parser->subcommands());
        $this->assertEquals('Shell to handle users and user groups', $parser->getDescription());
        $this->assertArrayKeysEqual(['help', 'quiet', 'verbose'], $parser->options());
    }
}
