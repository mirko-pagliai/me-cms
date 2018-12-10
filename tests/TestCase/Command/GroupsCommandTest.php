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
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * GroupsCommandTest class
 */
class GroupsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
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
        $this->loadModel('MeCms.UsersGroups');

        $this->exec('me_cms.groups');
        $this->assertExitWithSuccess();

        $expectedRows = $this->UsersGroups->find()->map(function ($row) {
            return [$row->id, $row->name, $row->label, $row->user_count];
        })->toList();
        $expectedRows[] = ['<info>ID</info>', '<info>Name</info>', '<info>Label</info>', '<info>Users</info>'];
        foreach ($expectedRows as $row) {
            $this->assertOutputContainsRow($row);
        }

        //Deletes all groups
        $this->UsersGroups->deleteAll(['id >=' => '1']);
        $this->exec('me_cms.groups');
        $this->assertExitWithError();
        $this->assertErrorContains('There are no user groups');
    }
}
