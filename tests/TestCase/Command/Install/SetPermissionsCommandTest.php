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
use MeTools\TestSuite\CommandTestCase;
use Tools\Filesystem;

/**
 * SetPermissionsCommandTest class
 */
class SetPermissionsCommandTest extends CommandTestCase
{
    /**
     * @uses \MeTools\Command\Install\SetPermissionsCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        $this->exec('me_cms.set_permissions -v');
        $this->assertExitSuccess();
        $expected = array_map(fn(string $path): string => 'Set permissions on `' . Filesystem::instance()->rtr($path) . '`', array_clean(Configure::read('WRITABLE_DIRS')));
        $this->assertSame($expected, $this->_out->messages());
    }
}
