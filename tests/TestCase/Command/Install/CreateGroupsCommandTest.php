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

namespace MeCms\Test\TestCase\Command\Install;

use MeTools\TestSuite\CommandTestCase;

/**
 * CreateGroupsCommandTest class
 * @property \MeCms\Command\Install\CreateGroupsCommand $Command
 * @property \Cake\Console\TestSuite\StubConsoleOutput|null $_err
 * @property \Cake\Console\TestSuite\StubConsoleInput|null $_in
 */
class CreateGroupsCommandTest extends CommandTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Users',
        'plugin.MeCms.UsersGroups',
    ];

    /**
     * @test
     * @uses \MeCms\Command\Install\CreateGroupsCommand::execute()
     */
    public function testExecute(): void
    {
        //A group already exists
        $this->exec('me_cms.create_groups -v');
        $this->assertExitSuccess();
        $this->assertOutputEmpty();
        $this->assertErrorContains('Some user groups already exist');

        //With no user groups
        $UsersGroups = $this->getTable('MeCms.UsersGroups');
        $UsersGroups->deleteAll(['id is NOT' => null]);
        $this->_in = $this->_err = null;
        $this->exec('me_cms.create_groups -v');
        $this->assertExitSuccess();
        $this->assertOutputContains('The user groups have been created');
        $this->assertErrorEmpty();
        $this->assertCount(3, $UsersGroups->find()->all());
    }
}
