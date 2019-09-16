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
?>

<div id="sidebar-accordion" role="tablist">
    <?php
    //Renders menus for MeCms
    echo $this->MenuBuilder->renderAsCollapse('MeCms', 'sidebar-accordion');

    //Renders menus for each plugin
    foreach (\MeCms\Core\Plugin::all(['exclude' => ['MeCms', 'MeTools', 'Assets', 'DatabaseBackup', 'Thumber']]) as $plugin) {
        $menus = $this->MenuBuilder->renderAsCollapse($plugin);

        if ($menus) {
            echo $this->Html->h6($plugin);
            echo $menus;
        }
    }
    ?>
</div>
