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

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use MeTools\TestSuite\CommandTestCase;

/**
 * CreateSamplePostCommandTest class
 * @property \MeCms\Command\Install\CreateSamplePostCommand $Command
 * @property \Cake\Console\TestSuite\StubConsoleOutput|null $_out
 */
class CreateSamplePostCommandTest extends CommandTestCase
{
    /**
     * @var array<string>
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.Users',
    ];

    /**
     * @test
     * @uses \MeCms\Command\Install\CreateSamplePostCommand::execute()
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.create_sample_post -v');
        $this->assertExitSuccess();
        $this->assertOutputContains('At least one post already exists');

        /** @var \MeCms\Model\Table\PostsTable $Posts */
        $Posts = $this->getTable('MeCms.Posts');
        $Posts->deleteAll(['id is NOT' => null]);

        $this->_out = $this->_err = null;
        $this->exec('me_cms.create_sample_post -v');
        $this->assertExitSuccess();
        $this->assertOutputContains('The sample post has been created');
        $this->assertFalse($Posts->find()->all()->isEmpty());

        $Posts->deleteAll(['id is NOT' => null]);

        $this->_out = $this->_err = null;
        $Posts->Users->deleteAll(['id is NOT' => null]);
        $this->exec('me_cms.create_sample_post -v');
        $this->assertErrorContains('You must first create a user. Run the `bin/cake me_cms.create_admin` command');
    }

    /**
     * Test for `execute()` method, on failure
     * @test
     * @uses \MeCms\Command\Install\CreateSamplePostCommand::execute()
     */
    public function testExecuteOnFailure(): void
    {
        /** @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject $Posts */
        $Posts = $this->getMockForModel('MeCms.Posts', ['save']);
        $Posts->method('save')->willReturn(false);
        $Posts->deleteAll(['id is NOT' => null]);

        $this->_err = new StubConsoleOutput();
        $this->assertSame(0, $this->Command->run(['-v'], new ConsoleIo(new StubConsoleOutput(), $this->_err)));
        $this->assertErrorContains(I18N_OPERATION_NOT_OK);
    }
}
