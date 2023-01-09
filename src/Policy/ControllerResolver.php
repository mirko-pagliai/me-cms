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
 * @since       2.31.0
 */

namespace MeCms\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\ResolverInterface;
use Cake\Controller\Controller;

/**
 * Policy resolver for controllers.
 * @see https://book.cakephp.org/authorization/2/en/policy-resolvers.html#creating-a-resolver
 */
class ControllerResolver implements ResolverInterface
{
    /**
     * Gets a policy for a controller.
     *
     * It returns the `ControllerHookPolicy` for all controllers.
     * @param \Cake\Controller\Controller $resource The resource
     * @return \MeCms\Policy\ControllerHookPolicy
     */
    public function getPolicy($resource): ControllerHookPolicy
    {
        if ($resource instanceof Controller) {
            return new ControllerHookPolicy();
        }

        throw new MissingPolicyException([get_class($resource)]);
    }
}
