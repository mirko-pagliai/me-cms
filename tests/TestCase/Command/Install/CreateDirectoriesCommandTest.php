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
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * CreateDirectoriesCommandTest class
 */
class CreateDirectoriesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Tests for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $pathsAlreadyExist = [
            getConfigOrFail('Assets.target'),
            getConfigOrFail('DatabaseBackup.target'),
            getConfigOrFail('Thumber.target'),
            TMP,
            WWW_ROOT . 'vendor',
        ];
        array_walk($pathsAlreadyExist, 'safe_mkdir');
        $pathsToBeCreated = array_diff(Configure::read('WRITABLE_DIRS'), $pathsAlreadyExist);
        foreach ($pathsToBeCreated as $path) {
            safe_rmdir_recursive($path);
            !file_exists($path) ?: $this->fail('Unable to delete ' . $path);
        }

        $this->exec('me_cms.create_directories -v');

        //Re-creates some files after execution
        foreach ([BANNERS, PHOTOS, USER_PICTURES] as $path) {
            safe_mkdir($path, 0777, true);
            file_put_contents($path . 'empty', '');
            chmod($path . 'empty', 0755);
        }

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
