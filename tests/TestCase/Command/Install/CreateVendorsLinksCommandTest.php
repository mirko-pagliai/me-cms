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
 * CreateVendorsLinksCommandTest class
 */
class CreateVendorsLinksCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Tests for `execute()` method
     * @test
     */
    public function testExecute()
    {
        $links = Configure::read('VENDOR_LINKS');

        foreach (array_keys($links) as $link) {
            $link = ROOT . 'vendor' . DS . $link;
            safe_mkdir($link, 0777, true);
            file_put_contents($link . DS . 'empty', null);
        }

        $this->exec('me_cms.create_vendors_links -v');
        $this->assertExitWithSuccess();

        foreach ($links as $link) {
            $this->assertOutputContains('Link `' . rtr(WWW_ROOT) . 'vendor' . DS . $link . '` has been created');
        }

        $this->assertErrorEmpty();
    }
}
