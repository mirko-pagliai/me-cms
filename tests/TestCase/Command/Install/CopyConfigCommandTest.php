<?php
/** @noinspection PhpUnhandledExceptionInspection */
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

use MeTools\Core\Configure;
use MeTools\TestSuite\CommandTestCase;
use Tools\Filesystem;

/**
 * CopyConfigCommandTest class
 */
class CopyConfigCommandTest extends CommandTestCase
{
    /**
     * @test
     * @uses \MeCms\Command\Install\CopyConfigCommand::execute()
     */
    public function testExecute(): void
    {
        $Filesystem = new Filesystem();

        $this->exec('me_cms.copy_config -v');
        $this->assertExitSuccess();
        $this->assertErrorEmpty();
        $expectedFiles = array_map(fn(string $file): string => $Filesystem->concatenate(CONFIG, pluginSplit($file)[1] . '.php'), Configure::read('MeCms.ConfigFiles'));
        foreach ($expectedFiles as $expectedFile) {
            $this->assertOutputContains('File or directory `' . $Filesystem->rtr($expectedFile) . '` already exists');
        }
    }
}
