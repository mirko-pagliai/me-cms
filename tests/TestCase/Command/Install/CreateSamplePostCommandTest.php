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
use Cake\TestSuite\Stub\ConsoleOutput;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * CreateSamplePostCommandTest class
 * @property \MeCms\Command\Install\CreateSamplePostCommand $Command
 */
class CreateSamplePostCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var bool
     */
    public $autoInitializeClass = true;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = [
        'plugin.MeCms.Posts',
        'plugin.MeCms.Users',
    ];

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.create_sample_post -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('At least one post already exists');

        $Posts = $this->getTable('MeCms.Posts');
        $Posts->deleteAll(['id is NOT' => null]);

        $this->_out = $this->_err = null;
        $this->exec('me_cms.create_sample_post -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('The sample post has been created');
        $this->assertFalse($Posts->find()->isEmpty());

        $Posts->deleteAll(['id is NOT' => null]);

        $this->_out = $this->_err = null;
        $this->getTable('MeCms.Users')->deleteAll(['id is NOT' => null]);
        $this->exec('me_cms.create_sample_post -v');
        $this->assertErrorContains('You must first create a user. Run the `bin/cake me_cms.create_admin` command');
    }

    /**
     * Test for `execute()` method, on failure
     * @test
     */
    public function testExecuteOnFailure(): void
    {
        /** @var \MeCms\Model\Table\PostsTable&\PHPUnit\Framework\MockObject\MockObject $Posts */
        $Posts = $this->getMockForModel('MeCms.Posts', ['save']);
        $Posts->method('save')->will($this->returnValue(false));
        $Posts->deleteAll(['id is NOT' => null]);

        $this->_err = new ConsoleOutput();
        $this->Command->Posts = $Posts;
        $this->assertSame(0, $this->Command->run(['-v'], new ConsoleIo(new ConsoleOutput(), $this->_err)));
        $this->assertErrorContains(I18N_OPERATION_NOT_OK);
    }
}
