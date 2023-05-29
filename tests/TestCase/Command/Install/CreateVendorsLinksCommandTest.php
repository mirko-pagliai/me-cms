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
 * CreateVendorsLinksCommandTest class
 */
class CreateVendorsLinksCommandTest extends CommandTestCase
{
    /**
     * @uses \MeTools\Command\Install\CreateVendorsLinksCommand::execute()
     * @test
     */
    public function testExecute(): void
    {
        Filesystem::instance()->mkdir(WWW_VENDOR);
        $this->exec('me_cms.create_vendors_links -v');
        Filesystem::instance()->remove(WWW_VENDOR);
        $this->assertExitSuccess();
        foreach (Configure::read('MeCms.VendorLinks') as $expectedOrigin => $expectedTarget) {
            $expectedOrigin = VENDOR . $expectedOrigin;
            $this->assertOutputContains(file_exists($expectedOrigin) ? 'Link to `' . rtr(WWW_VENDOR . $expectedTarget) . '` has been created' : 'File or directory `' . rtr($expectedOrigin) . '` does not exist');
        }
        $this->assertErrorEmpty();
    }
}
