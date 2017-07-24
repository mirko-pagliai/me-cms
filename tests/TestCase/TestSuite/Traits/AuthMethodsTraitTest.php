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
use MeCms\TestSuite\Traits\AuthMethodsTrait;

/**
 * AuthMethodsTraitTest class
 */
class AuthMethodsTraitTest extends TestCase
{
    use AuthMethodsTrait;

    /**
     * Test for `assertGroupsAreAuthorized()` method on failure
     * @expectedException \PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessage The property `$this->Controller` has not been set
     * @test
     */
    public function testAssertGroupsAreAuthorizedFailure()
    {
        $this->assertGroupsAreAuthorized(null);
    }

    /**
     * Test for `assertUsersAreAuthorized()` method on failure
     * @expectedException \PHPUnit\Framework\AssertionFailedError
     * @expectedExceptionMessage The property `$this->Controller` has not been set
     * @test
     */
    public function testAssertUsersAreAuthorizedFailure()
    {
        $this->assertUsersAreAuthorized(null);
    }
}
