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
?>

<div id="sidebar-accordion" role="tablist">
    <?php
    $menus = [];
    foreach (Plugin::all(['mecms_core' => false]) as $plugin) {
        $menus += $this->MenuBuilder->generate($plugin);
    }

    //Echoes posts and pages menus
    echo $this->MenuBuilder->renderAsCollapse($menus['MeCms.posts'], 'sidebar-accordion');
    echo $this->MenuBuilder->renderAsCollapse($menus['MeCms.pages'], 'sidebar-accordion');
    unset($menus['MeCms.posts'], $menus['MeCms.pages']);

    //Echoes all the remaining menus, sorted by title
    $titles = array_column($menus, 'title');
    array_multisort($titles, SORT_ASC, $menus);
    foreach ($menus as $menu) {
        echo $this->MenuBuilder->renderAsCollapse($menu, 'sidebar-accordion');
    }
    ?>
</div>
