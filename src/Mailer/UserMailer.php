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
     * @see MeCms\Controller\Admin\UsersController::activationResend()
     * @see MeCms\Controller\Admin\UsersController::signup()
     * @throws InternalErrorException
     */
    public function activation($user)
    {
        //Checks that all required data is present
        foreach (['email', 'full_name'] as $property) {
            if (empty($user->$property)) {
                throw new InternalErrorException(__d('me_cms', 'Missing `{0}` property from data', $property));
            }
        }

        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Activate your account'))
            ->setTemplate(ME_CMS . '.Users/activation')
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
            ->setTemplate(ME_CMS . '.Users/change_password')
            ->setViewVars(['fullName' => $user->full_name]);
    }

    /**
     * Email to ask a new password.
     *
     * The `$user` object must contain the `email` and `full_name` properties
     * @param \MeCms\Model\Entity\User $user User data
     * @return void
     * @see MeCms\Controller\UsersController::passwordForgot()
     * @throws InternalErrorException
     */
    public function passwordForgot($user)
    {
        //Checks that all required data is present
        foreach (['email', 'full_name'] as $property) {
            if (empty($user->$property)) {
                throw new InternalErrorException(__d('me_cms', 'Missing `{0}` property from data', $property));
            }
        }

        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Reset your password'))
            ->setTemplate(ME_CMS . '.Users/password_forgot')
            ->setViewVars(['fullName' => $user->full_name]);
    }
}
