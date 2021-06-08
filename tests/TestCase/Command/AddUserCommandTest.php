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

use Cake\ORM\TableRegistry;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * AddUserCommandTest class
 */
class AddUserCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

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
        /** @var \MeCms\Model\Table\UsersTable $Users */
        $Users = TableRegistry::getTableLocator()->get('MeCms.Users');
        $example = ['myusername', 'password1/', 'password1/', 'mail@example.com', 'Alfa', 'Beta'];

        $expectedUserId = $Users->find()->extract('id')->last() + 1;
        $this->exec('me_cms.add_user', array_merge($example, ['3']));
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<question>Group ID</question>');
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . $expectedUserId . '</success>');
        $this->assertErrorEmpty();

        //Checks the user has been created
        $this->assertEquals(3, $Users->findById($expectedUserId)->extract('group_id')->first());
        $Users->delete($Users->get($expectedUserId));

        //Tries using the `group` option
        $this->_in = null;
        $this->exec('me_cms.add_user --group 2', $example);
        $this->assertExitWithSuccess();
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . ++$expectedUserId . '</success>');
        $this->assertErrorEmpty();

        //Checks the user has been created
        $this->assertEquals(2, $Users->findById($expectedUserId)->extract('group_id')->first());

        //Tries with a no existing group
        $this->_in = null;
        $this->exec('me_cms.add_user --group 123', $example);
        $this->assertExitWithSuccess();
        $this->assertErrorContains('Invalid group ID');

        //Tries with empty data
        $this->_in = null;
        $this->exec('me_cms.add_user -v', ['', '', '', '', '', '', '']);
        $this->assertExitWithSuccess();
        $this->assertErrorContains('Field `username` is empty. Try again');

        //Tries with wrong data
        $this->_in = null;
        $this->exec('me_cms.add_user -v', ['ab', 'password', 'password2', 'mail', 'aa', 'bb', '3']);
        $this->assertExitWithSuccess();
        $this->assertErrorContains('The operation has not been performed correctly');
        $this->assertErrorContains('The user could not be saved');
        $this->assertErrorContains('Field `email`: you have to enter a valid value');
        $this->assertErrorContains('Field `first_name`: must be between 3 and 40 chars');
        $this->assertErrorContains('Field `first_name`: allowed chars: letters, apostrophe, space. Has to begin with a capital letter');
        $this->assertErrorContains('Field `last_name`: must be between 3 and 40 chars');
        $this->assertErrorContains('Field `last_name`: allowed chars: letters, apostrophe, space. Has to begin with a capital letter');
        $this->assertErrorContains('Field `username`: must be between 4 and 40 chars');
        $this->assertErrorContains('Field `username`: allowed chars: ' . I18N_LOWERCASE_NUMBERS_DASH);
        $this->assertErrorContains('Field `password`: the password should contain letters, numbers and symbols');
        $this->assertErrorContains('Field `password_repeat`: passwords don\'t match');

        //Tries with no groups
        $Users->Groups->deleteAll(['id IS NOT' => null]);
        $this->_in = null;
        $this->exec('me_cms.add_user -v');
        $this->assertExitWithSuccess();
        $this->assertErrorContains('Before you can manage users, you have to create at least a user group');
    }
}
