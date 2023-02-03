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

namespace MeCms\Test\TestCase\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use MeCms\Controller\AppController;
use MeCms\Policy\ControllerHookPolicy;
use MeCms\Policy\ControllerResolver;
use MeCms\TestSuite\TestCase;

/**
 * ControllerResolverTest class
 */
class ControllerResolverTest extends TestCase
{
    /**
     * Test for `getPolicy()` method
     * @uses \MeCms\Policy\ControllerResolver::getPolicy()
     * @test
     */
    public function testGetPolicy(): void
    {
        $Resolver = new ControllerResolver();

        $Result = $Resolver->getPolicy($this->getMockForAbstractClass(AppController::class));
        $this->assertInstanceOf(ControllerHookPolicy::class, $Result);

        $this->expectException(MissingPolicyException::class);
        $this->expectExceptionMessage('Policy for `stdClass` has not been defined');
        $Resolver->getPolicy(new \stdClass());
    }
}
