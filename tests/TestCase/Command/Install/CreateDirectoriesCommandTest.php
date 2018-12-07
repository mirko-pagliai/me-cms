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

use Cake\Core\Configure;
use MeCms\TestSuite\ConsoleIntegrationTestCase;

/**
 * CreateDirectoriesCommandTest class
 */
class CreateDirectoriesCommandTest extends ConsoleIntegrationTestCase
{
    /**
     * Tests for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $pathsAlreadyExist = [TMP, TMP . 'cache', WWW_ROOT . 'vendor'];
        array_walk($pathsAlreadyExist, 'safe_mkdir');
        $pathsToBeCreated = array_diff(Configure::read('WRITABLE_DIRS'), $pathsAlreadyExist);
        array_walk($pathsToBeCreated, 'safe_rmdir');

        $this->exec('me_cms.create_directories -v');
        $this->assertExitWithSuccess();

        foreach ($pathsAlreadyExist as $path) {
            $this->assertOutputContains('File or directory `' . rtr($path) . '` already exists');
        }

        foreach ($pathsToBeCreated as $path) {
            $this->assertOutputContains('Created `' . rtr($path) . '` directory');
            $this->assertOutputContains('Setted permissions on `' . rtr($path) . '`');
        }

        $this->assertErrorEmpty();
    }
}
