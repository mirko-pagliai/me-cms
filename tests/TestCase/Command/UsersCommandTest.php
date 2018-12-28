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
namespace MeCms\Test\TestCase\Command;

use Cake\I18n\Time;
use MeCms\Model\Entity\User;
use MeCms\Model\Entity\UsersGroup;
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
        $Users = $this->getMockForModel('MeCms.Users', null);

        $expectedRows = $Users->find()->contain('Groups')->map(function (User $user) {
            if ($user->banned) {
                $user->status = __d('me_cms', 'Banned');
            } elseif (!$user->active) {
                $user->status = __d('me_cms', 'Pending');
            } else {
                $user->status = __d('me_cms', 'Active');
            }

            $user->set('created', $user->created instanceof Time ? $user->created->i18nFormat('yyyy/MM/dd HH:mm') : $user->created);
            $user->set('group', $user->group instanceof UsersGroup ? $user->group->label : $user->group);

            return $user->extract(['id', 'username', 'group', 'full_name', 'email', 'post_count', 'status', 'created']);
        })->toList();
        $expectedRows[] = ['<info>ID</info>', '<info>Username</info>', '<info>Group</info>', '<info>Name</info>', '<info>Email</info>', '<info>Posts</info>', '<info>Status</info>', '<info>Date</info>'];

        $this->exec('me_cms.users');
        $this->assertExitWithSuccess();
        array_walk($expectedRows, [$this, 'assertOutputContainsRow']);

        //Deletes all users
        $Users->deleteAll(['id >=' => '1']);
        $this->exec('me_cms.users');
        $this->assertExitWithSuccess();
        $this->assertErrorContains('There are no users');
    }
}
