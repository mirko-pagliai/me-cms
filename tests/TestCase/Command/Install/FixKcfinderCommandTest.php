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

use MeCms\TestSuite\TestCase;
use MeTools\TestSuite\ConsoleIntegrationTestTrait;

/**
 * FixKcfinderCommandTest class
 */
class FixKcfinderCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Test for `execute()` method
     * @test
     */
    public function testExecute()
    {
        //For now KCFinder is not available
        $this->exec('me_cms.fix_kcfinder -v');
        $this->assertExitWithSuccess();
        $this->assertOutputEmpty();
        $this->assertErrorContains('KCFinder is not available');

        //Now KCFinder is installed
        $expected = 'php_value session.cache_limiter must-revalidate' . PHP_EOL .
            'php_value session.cookie_httponly On' . PHP_EOL .
            'php_value session.cookie_lifetime 14400' . PHP_EOL .
            'php_value session.gc_maxlifetime 14400' . PHP_EOL .
            'php_value session.name CAKEPHP';
        create_kcfinder_files(false);
        $this->exec('me_cms.fix_kcfinder -v');
        $this->assertExitWithSuccess();
        $this->assertOutputContains('Creating file ' . KCFINDER . '.htaccess');
        $this->assertOutputContains('<success>Wrote</success> `' . KCFINDER . '.htaccess`');
        $this->assertErrorEmpty();
        $this->assertStringEqualsFile(KCFINDER . '.htaccess', $expected);
    }
}
