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
namespace MeCms\Test\TestCase\Command\Install;

use MeCms\TestSuite\ConsoleIntegrationTestCase;

/**
 * CopyConfigCommandTest class
 */
class CopyConfigCommandTest extends ConsoleIntegrationTestCase
{
    /**
     * If `true`, a mock instance of the shell will be created
     * @var bool
     */
    protected $autoInitializeClass = true;

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $this->exec('me_cms.copy_config -v');
        $this->assertExitWithSuccess();

        foreach ($this->Shell->config as $file) {
            $this->assertOutputContains('File or directory `' . rtr(CONFIG . pluginSplit($file)[1] . '.php') . '` already exists');
        }
    }
}
