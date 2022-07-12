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

use MeCms\Core\Plugin;

$this->extend('MeCms.common/userbar');

$list[] = $this->Html->li($this->Html->link(__d('me_cms', 'Homepage'), ['_name' => 'homepage'], ['class' => 'nav-link', 'icon' => 'home', 'target' => '_blank']), ['class' => 'nav-item']);

foreach (Plugin::all(['mecms_core' => false]) as $plugin) {
    //Creates a `<li>` tag with a dropdown for each menu of each plugin
    foreach ($this->MenuBuilder->generate($plugin) as $menu) {
        $titleOptions = optionsParser($menu['titleOptions'])->append('class', 'nav-link');
        $this->Dropdown->start($menu['title'], $titleOptions->toArray());
        array_map(fn(array $link) => call_user_func_array([$this->Dropdown, 'link'], $link), $menu['links']);
        $list[] = $this->Html->li($this->Dropdown->end(), ['class' => 'nav-item dropdown']);
    }
}

echo $this->Html->ul($list, ['class' => 'navbar-nav me-auto']);
