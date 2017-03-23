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
namespace MeCms\Mailer;

use Cake\Network\Exception\InternalErrorException;
use MeCms\Mailer\Mailer;

/**
 * UserMailer class
 */
class UserMailer extends Mailer
{
    /**
     * Email to activate the user account (signup and resend activation).
     *
     * The `$user` object must contain the `email` and `full_name` properties
     * @param \MeCms\Model\Entity\User $user User data
     * @return void
     * @see MeCms\Controller\Admin\UsersController::resendActivation()
     * @see MeCms\Controller\Admin\UsersController::signup()
     * @throws InternalErrorException
     */
    public function activateAccount($user)
    {
        //Checks that all required data is present
        foreach (['email', 'full_name'] as $property) {
            if (empty($user->$property)) {
                throw new InternalErrorException(__d('me_cms', 'Missing `{0}` property from data', $property));
            }
        }

        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Activate your account'))
            ->setTemplate('MeCms.Users/activate_account')
            ->setViewVars(['fullName' => $user->full_name]);
    }

    /**
     * Email to change the user's password.
     *
     * The `$user` object must contain the `email` and `full_name` properties
     * @param \MeCms\Model\Entity\User $user User data
     * @return void
     * @see MeCms\Controller\Admin\UsersController::changePassword()
     * @throws InternalErrorException
     */
    public function changePassword($user)
    {
        //Checks that all required data is present
        foreach (['email', 'full_name'] as $property) {
            if (empty($user->$property)) {
                throw new InternalErrorException(__d('me_cms', 'Missing `{0}` property from data', $property));
            }
        }

        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Your password has been changed'))
            ->setTemplate('MeCms.Users/change_password')
            ->setViewVars(['fullName' => $user->full_name]);
    }

    /**
     * Email to ask a new password.
     *
     * The `$user` object must contain the `email` and `full_name` properties
     * @param \MeCms\Model\Entity\User $user User data
     * @return void
     * @see MeCms\Controller\UsersController::forgotPassword()
     * @throws InternalErrorException
     */
    public function forgotPassword($user)
    {
        //Checks that all required data is present
        foreach (['email', 'full_name'] as $property) {
            if (empty($user->$property)) {
                throw new InternalErrorException(__d('me_cms', 'Missing `{0}` property from data', $property));
            }
        }

        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Reset your password'))
            ->setTemplate('MeCms.Users/forgot_password')
            ->setViewVars(['fullName' => $user->full_name]);
    }
}
