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
 * CreateDirectoriesCommandTest class
 */
class CreateDirectoriesCommandTest extends CommandTestCase
{
    /**
     * @uses \MeTools\Command\Install\CreateDirectoriesCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.create_directories -v');
        $this->assertExitSuccess();
        foreach (Configure::read('MeCms.WritableDirs') as $expectedDir) {
            $this->assertOutputContains('File or directory `' . Filesystem::instance()->rtr($expectedDir) . '` already exists');
        }
    }
}
