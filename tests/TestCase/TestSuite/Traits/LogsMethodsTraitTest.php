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
namespace MeCms\Test\TestCase\TestSuite\Traits;

use Cake\TestSuite\TestCase;
use MeCms\TestSuite\Traits\LogsMethodsTrait;

/**
 * LogsMethodsTraitTest class
 */
class LogsMethodsTraitTest extends TestCase
{
    use LogsMethodsTrait;

    /**
     * Test for `assertLogContains()` method on failure
     * @expectedException \PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessage Log file /tmp/me_cms/cakephp_log/noExisting.log not readable
     * @test
     */
    public function testAssertLogContainsFailure()
    {
        $this->assertLogContains('value', 'noExisting');
    }
}
