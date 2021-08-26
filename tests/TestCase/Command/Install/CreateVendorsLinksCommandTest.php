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
 * CreateVendorsLinksCommandTest class
 */
class CreateVendorsLinksCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Tests for `execute()` method
     * @test
     */
    public function testExecute(): void
    {
        $expected = array_values(array_filter(array_map(function (string $target, string $origin): string {
            $target = WWW_ROOT . 'vendor' . DS . $target;
            if (file_exists($target)) {
                return 'File or directory `' . (new Filesystem())->rtr($target) . '` already exists';
            }

            return file_exists(ROOT . 'vendor' . DS . $origin) ? 'Link `' . (new Filesystem())->rtr($target) . '` has been created' : '';
        }, Configure::read('VENDOR_LINKS'), array_keys(Configure::read('VENDOR_LINKS')))));

        $this->exec('me_cms.create_vendors_links -v');
        $this->assertSame($expected, $this->_out->messages());
    }
}
