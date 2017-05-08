<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
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
