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
 *
 * @var \MeCms\View\View\AppView $this
 */
$menu = [];
$url = ['_name' => 'login'];

if (!$this->getRequest()->is('url', $url)) {
    $menu[] = $this->Html->link(__d('me_cms', 'Login'), $url);
}

//If signup is enabled
$url = ['_name' => 'signup'];
if (getConfig('users.signup') && !$this->getRequest()->is('url', $url)) {
    $menu[] = $this->Html->link(__d('me_cms', 'Sign up'), $url);
}

//If signup is enabled and if accounts will be enabled by the user via email
$url = ['_name' => 'activationResend'];
if (getConfig('users.signup') && getConfig('users.activation') === 1 && !$this->getRequest()->is('url', $url)) {
    $menu[] = $this->Html->link(__d('me_cms', 'Resend activation email'), $url);
}

//If reset password is enabled
$url = ['_name' => 'passwordForgot'];
if (getConfig('users.reset_password') && !$this->getRequest()->is('url', $url)) {
    $menu[] = $this->Html->link(__d('me_cms', 'Forgot your password?'), $url);
}

if ($menu) {
    echo $this->Html->ul($menu, ['class' => 'list-unstyled mt-3 mb-0']);
}
