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
namespace Test\TestCase\Command;

use Cake\Datasource\ModelAwareTrait;
use MeCms\TestSuite\ConsoleIntegrationTestCase;

/**
 * UsersCommandTest class
 */
class UsersCommandTest extends ConsoleIntegrationTestCase
{
    use ModelAwareTrait;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.me_cms.Users',
        'plugin.me_cms.UsersGroups',
    ];

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $this->loadModel('MeCms.Users');

        $expectedRows = $this->Users->find()->contain('Groups')->map(function ($user) {
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
        $this->Users->deleteAll(['id >=' => '1']);
        $this->exec('me_cms.user users');
        $this->assertExitWithError();
        $this->assertErrorContains('There are no users');
    }
}
