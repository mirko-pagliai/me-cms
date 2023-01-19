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

use Cake\Core\Configure;
use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;
use Tools\Filesystem;

/**
 * CreateDirectoriesCommandTest class
 */
class CreateDirectoriesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @uses \MeTools\Command\Install\CreateDirectoriesCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.create_directories -v');
        $this->assertExitSuccess();
        $expected = array_map(fn(string $path): string => 'File or directory `' . Filesystem::instance()->rtr($path) . '` already exists', array_clean(Configure::read('WRITABLE_DIRS')));
        $this->assertSame($expected, $this->_out->messages());
    }
}
