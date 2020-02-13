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

use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\TestSuite\Stub\ConsoleOutput;
use MeCms\TestSuite\TestCase;
use MeTools\Command\Install\CreateVendorsLinksCommand;
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
        $io = new ConsoleIo(new ConsoleOutput(), new ConsoleOutput());
        $Command = $this->getMockBuilder(CreateVendorsLinksCommand::class)
            ->setMethods(['createLink'])
            ->getMock();

        $count = 0;
        foreach (Configure::read('VENDOR_LINKS') as $origin => $target) {
            $Command->expects($this->at($count++))
                ->method('createLink')
                ->with($io, ROOT . 'vendor' . DS . $origin, WWW_ROOT . 'vendor' . DS . $target);
        }

        $Command->expects($this->exactly(count(Configure::read('VENDOR_LINKS'))))
            ->method('createLink');

        $this->assertNull($Command->run([], $io));
    }
}
