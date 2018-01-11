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
use MeCms\Core\Plugin;

$this->extend('/Admin/Common/Element/userbar');

$menus[] = $this->Html->link(__d('me_cms', 'Homepage'), ['_name' => 'homepage'], [
    'class' => 'nav-link',
    'icon' => 'home',
    'target' => '_blank',
]);

//Renders menus for each plugin
foreach (Plugin::all(['exclude' => [ME_TOOLS, ASSETS, DATABASE_BACKUP, THUMBER]]) as $plugin) {
    $menus += $this->MenuBuilder->renderAsDropdown($plugin, ['class' => 'nav-link d-lg-none']);
}

echo $this->Html->ul($menus, ['class' => 'navbar-nav mr-auto'], ['class' => 'dropdown nav-item']);