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
?>

<div id="sidebar-accordion" role="tablist">
    <?php
    //Renders menus for MeCms
    echo $this->MenuBuilder->renderAsCollapse(ME_CMS);

    //Renders menus for each plugin
    foreach (Plugin::all(['exclude' => [ME_CMS, 'MeTools', 'Assets', 'DatabaseBackup', 'Thumber']]) as $plugin) {
        $menus = $this->MenuBuilder->renderAsCollapse($plugin);

        if (!empty($menus)) {
            echo $this->Html->h6($plugin);
            echo $menus;
        }
    }
    ?>
</div>
