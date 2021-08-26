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

use Cake\Core\Configure;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;
use Tools\Filesystem;

/**
 * CopyConfigCommandTest class
 */
class CopyConfigCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.copy_config -v');
        $this->assertExitWithSuccess();
        foreach (Configure::read('CONFIG_FILES') as $file) {
            $this->assertOutputContains('File or directory `' . (new Filesystem())->rtr(CONFIG . pluginSplit($file)[1] . '.php') . '` already exists');
        }
    }
}
