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
namespace MeCms\Mailer;

use MeCms\Mailer\Mailer;
use MeCms\Model\Entity\User;

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
     */
    public function activation(User $user)
    {
        //Checks that all required data is present
        key_exists_or_fail(['email', 'full_name'], $user->toArray());

        $this->viewBuilder()->setTemplate('MeCms.Users/activation');
        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Activate your account'))
            ->setViewVars(['fullName' => $user->full_name]);
    }

    /**
     * Email to change the user's password.
     *
     * The `$user` object must contain the `email` and `full_name` properties
     * @param \MeCms\Model\Entity\User $user User data
     * @return void
     * @see MeCms\Controller\Admin\UsersController::changePassword()
     */
    public function changePassword(User $user)
    {
        //Checks that all required data is present
        key_exists_or_fail(['email', 'full_name'], $user->toArray());

        $this->viewBuilder()->setTemplate('MeCms.Users/change_password');
        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Your password has been changed'))
            ->setViewVars(['fullName' => $user->full_name]);
    }

    /**
     * Email to ask a new password.
     *
     * The `$user` object must contain the `email` and `full_name` properties
     * @param \MeCms\Model\Entity\User $user User data
     * @return void
     * @see MeCms\Controller\UsersController::passwordForgot()
     */
    public function passwordForgot(User $user)
    {
        //Checks that all required data is present
        key_exists_or_fail(['email', 'full_name'], $user->toArray());

        $this->viewBuilder()->setTemplate('MeCms.Users/password_forgot');
        $this->setTo([$user->email => $user->full_name])
            ->setSubject(__d('me_cms', 'Reset your password'))
            ->setViewVars(['fullName' => $user->full_name]);
    }
}
