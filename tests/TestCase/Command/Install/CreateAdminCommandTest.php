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

use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * CreateAdminCommandTest class
 */
class CreateAdminCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

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
        $example = ['my-username', 'password1/', 'password1/', 'mail@example.com', 'Alfa', 'Beta'];
        /** @var \MeCms\Model\Table\UsersTable $Users */
        $Users = $this->getTable('MeCms.Users');

        $expectedUserId = $Users->find()->all()->extract('id')->last() + 1;
        $this->exec('me_cms.create_admin', $example);
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>The operation has been performed correctly</success>');
        $this->assertOutputContains('<success>The user was created with ID ' . $expectedUserId . '</success>');
        $this->assertErrorEmpty();

        //Checks the user has been created
        $this->assertEquals(1, $Users->findById($expectedUserId)->all()->extract('group_id')->first());
    }
}
