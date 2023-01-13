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

if (!getConfig('users.userbar') || !$this->Identity->isLoggedIn()) {
    return;
}

$this->extend('MeCms.common/userbar');

echo $this->Html->ul([
    $this->Html->link(__d('me_cms', 'Dashboard'), ['_name' => 'dashboard'], ['class' => 'nav-link', 'icon' => 'dashboard']),
], ['class' => 'navbar-nav me-auto'], ['class' => 'nav-item']);
