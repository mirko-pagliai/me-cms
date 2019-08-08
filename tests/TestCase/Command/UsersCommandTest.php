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
use MeCms\Command\UsersCommand;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * UsersCommandTest class
 */
class UsersCommandTest extends TestCase
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
    public function testExecute()
    {
        $command = new UsersCommand();
        $command->Users = TableRegistry::getTableLocator()->get('MeCms.Users');
        $expectedRows = $this->invokeMethod($command, 'getUsersForTable');
        array_unshift($expectedRows, ['<info>ID</info>', '<info>Username</info>', '<info>Group</info>', '<info>Name</info>', '<info>Email</info>', '<info>Posts</info>', '<info>Status</info>', '<info>Date</info>']);

        $this->exec('me_cms.users');
        $this->assertExitWithSuccess();
        array_walk($expectedRows, [$this, 'assertOutputContainsRow']);

        //Deletes all users
        $command->Users->deleteAll(['id IS NOT' => null]);
        $this->exec('me_cms.users');
        $this->assertExitWithSuccess();
        $this->assertErrorContains('There are no users');
    }
}
