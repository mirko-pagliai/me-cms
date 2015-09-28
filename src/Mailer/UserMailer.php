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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
namespace MeCms\Mailer;

use MeCms\Mailer\Mailer;

/**
 * UserMailer class
 */
class UserMailer extends Mailer {
	/**
	 * Sends activation mail (signup and resend activation)
	 * @param array $user User data
	 * @see MeCms\Controller\Admin\UsersController::resend_activation()
	 * @see MeCms\Controller\Admin\UsersController::signup()
	 */
	public function activation_mail($user) {
		$this->to([$user->email => $user->full_name])
			->set(['full_name' => $user->full_name])
			->subject(__d('me_cms', 'Activate your account'))
			->template('MeCms.Users/activate_account');
	}
	
	/**
	 * Changes the user's password
	 * @param array $user User data
	 * @see MeCms\Controller\Admin\UsersController::change_password()
	 */
	public function change_password($user) {
		$this->to([$user->email => $user->full_name])
			->set(['full_name' => $user->full_name])
			->subject(__d('me_cms', 'Your password has been changed'))
			->template('MeCms.Users/change_password');
	}
	
	/**
	 * Requests a new password
	 * @param array $user User data
	 * @see MeCms\Controller\UsersController::forgot_password()
	 */
	public function forgot_password($user) {
        $this->to([$user->email => $user->full_name])
			->set(['full_name' => $user->full_name])
            ->subject(__d('me_cms', 'Reset your password'))
			->template('MeCms.Users/forgot_password');
	}
}