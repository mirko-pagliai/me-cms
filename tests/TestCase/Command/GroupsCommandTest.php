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

use MeCms\Model\Entity\UsersGroup;
use MeTools\TestSuite\CommandTestCase;

/**
 * GroupsCommandTest class
 */
class GroupsCommandTest extends CommandTestCase
{
    /**
     * @var array<string>
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
        $UsersGroups = $this->getTable('MeCms.UsersGroups');

        $expectedRows = $UsersGroups->find()
            ->select(['id', 'name', 'label', 'user_count'])
            ->all()
            ->map(fn(UsersGroup $group): array => array_map('strval', $group->toArray()))
            ->toList();
        $expectedRows[] = ['<info>ID</info>', '<info>Name</info>', '<info>Label</info>', '<info>Users</info>'];

        $this->exec('me_cms.groups');
        $this->assertExitSuccess();
        array_walk($expectedRows, [$this, 'assertOutputContainsRow']);

        //Deletes all user groups
        $UsersGroups->deleteAll(['id IS NOT' => null]);
        $this->exec('me_cms.groups');
        $this->assertExitSuccess();
        $this->assertErrorContains('There are no user groups');
    }
}
