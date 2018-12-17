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

use Cake\Datasource\ModelAwareTrait;
use MeCms\Model\Entity\User;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * UsersCommandTest class
 */
class UsersCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use ModelAwareTrait;

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
        $this->loadModel('MeCms.Users');

        $this->exec('me_cms.users');
        $this->assertExitWithSuccess();

        $expectedRows = $this->Users->find()->contain('Groups')->map(function (User $user) {
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
        })->toList();
        $expectedRows[] = ['<info>ID</info>', '<info>Username</info>', '<info>Group</info>', '<info>Name</info>', '<info>Email</info>', '<info>Posts</info>', '<info>Status</info>', '<info>Date</info>'];
        foreach ($expectedRows as $row) {
            $this->assertOutputContainsRow($row);
        }

        //Deletes all users
        $this->Users->deleteAll(['id >=' => '1']);
        $this->exec('me_cms.users');
        $this->assertExitWithError();
        $this->assertErrorContains('There are no users');
    }
}
