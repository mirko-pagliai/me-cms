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

$menu = [];

if (!$this->request->isUrl($url = ['_name' => 'login'])) {
    $menu[] = $this->Html->link(__d('me_cms', 'Login'), $url);
}

//If signup is enabled
if (config('users.signup') &&
    !$this->request->isUrl($url = ['_name' => 'signup'])
) {
    $menu[] = $this->Html->link(__d('me_cms', 'Sign up'), $url);
}

//If signup is enabled and if accounts will be enabled by the user via email
if (config('users.signup') && config('users.activation') === 1 &&
    !$this->request->isUrl($url = ['_name' => 'resendActivation'])
) {
    $menu[] = $this->Html->link(__d('me_cms', 'Resend activation email'), $url);
}

//If reset password is enabled
if (config('users.reset_password') && !$this->request->isUrl($url = ['_name' => 'forgotPassword'])) {
    $menu[] = $this->Html->link(__d('me_cms', 'Forgot your password?'), $url);
}

echo $this->Html->ul($menu, ['class' => 'actions']);
