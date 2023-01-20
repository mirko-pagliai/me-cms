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
 * CopyConfigCommandTest class
 */
class CopyConfigCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @uses \MeCms\Command\Install\CopyConfigCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.copy_config -v');
        $this->assertExitSuccess();
        $expected = array_map(fn(string $path): string => 'File or directory `' . Filesystem::instance()->rtr(CONFIG . pluginSplit($path)[1] . '.php') . '` already exists', array_clean(Configure::read('CONFIG_FILES')));
        $this->assertSame($expected, $this->_out->messages());
    }
}
