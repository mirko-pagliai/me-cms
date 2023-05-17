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
        $expectedLinks = array_map(fn(string $link): string => Filesystem::concatenate(WWW_ROOT, 'vendor', $link), Configure::read('MeCms.VendorLinks'));
        $expectedLinks = array_map(fn(string $link): string => file_exists($link) ? $link : Filesystem::createFile($link), $expectedLinks);

        $this->exec('me_cms.create_vendors_links -v');
        $this->assertExitSuccess();
        foreach ($expectedLinks as $expectedLink) {
            $this->assertOutputContains('File or directory `' . rtr($expectedLink) . '` already exists');
        }
    }
}
