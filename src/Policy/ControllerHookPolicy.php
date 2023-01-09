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

/**
 * ControllerHook policy.
 *
 * This policy class uses `__call()`, so that it can handle all the actions in the controller. The policy calls the
 *  `isAuthorized()` method on our controller, giving us backwards compatibility with our existing logic.
 * @see https://book.cakephp.org/authorization/2/en/policy-resolvers.html#creating-a-resolver
 */
class ControllerHookPolicy
{
    /**
     * Magic method for the policy.
     *
     * It calls the `isAuthorized()` method on our controller
     * @param string $name
     * @param array{\Authorization\Identity, \MeCms\Controller\Admin\AppController} $arguments
     * @return bool
     */
    public function __call(string $name, array $arguments): bool
    {
        [$Identity, $Controller] = $arguments;

        /** @var \MeCms\Model\Entity\User $User */
        $User = $Identity->getOriginalData();

        return $Controller->isAuthorized($User);
    }
}
