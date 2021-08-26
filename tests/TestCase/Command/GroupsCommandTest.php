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
use MeCms\Model\Entity\UsersGroup;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * GroupsCommandTest class
 */
class GroupsCommandTest extends TestCase
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
        $UsersGroups = TableRegistry::getTableLocator()->get('MeCms.UsersGroups');

        $expectedRows = $UsersGroups->find()
            ->select(['id', 'name', 'label', 'user_count'])
            ->map(function (UsersGroup $group) {
                return array_map('strval', $group->toArray());
            })
            ->toList();
        $expectedRows[] = ['<info>ID</info>', '<info>Name</info>', '<info>Label</info>', '<info>Users</info>'];

        $this->exec('me_cms.groups');
        $this->assertExitWithSuccess();
        array_walk($expectedRows, [$this, 'assertOutputContainsRow']);

        //Deletes all groups
        $UsersGroups->deleteAll(['id IS NOT' => null]);
        $this->exec('me_cms.groups');
        $this->assertExitWithSuccess();
        $this->assertErrorContains('There are no user groups');
    }
}
