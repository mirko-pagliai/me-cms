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

<nav id="topbar" class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#topbar-collapse">
                <span class="sr-only"><?= __d('me_cms', 'Toggle navigation') ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="topbar-collapse">
            <?php
                $menus = [
                    $this->Html->link(__d('me_cms', 'Homepage'), ['_name' => 'homepage'], [
                        'icon' => 'home',
                        'target' => '_blank',
                    ]),
                ];

                echo $this->Html->ul($menus, ['class' => 'nav navbar-nav hidden-xs hidden-sm']);

                //Renders menus for each plugin
                foreach (Plugin::all(['exclude' => [METOOLS, ASSETS, DATABASE_BACKUP, THUMBER]]) as $plugin) {
                    $menus += $this->MenuBuilder->renderAsDropdown($plugin);
                }

                echo $this->Html->ul($menus, ['class' => 'nav navbar-nav visible-xs visible-sm']);

                $userMenu[] = call_user_func(function () {
                    $this->Dropdown->start($this->Auth->user('full_name'), ['icon' => 'user']);

                    if (getConfig('users.login_log')) {
                        echo $this->Html->link(
                            I18N_LAST_LOGIN,
                            ['controller' => 'Users', 'action' => 'lastLogin', 'plugin' => ME_CMS]
                        );
                    }

                    echo $this->Html->link(
                        __d('me_cms', 'Change password'),
                        ['controller' => 'Users', 'action' => 'changePassword', 'plugin' => ME_CMS]
                    );
                    echo $this->Html->link(__d('me_cms', 'Logout'), ['_name' => 'logout']);

                    return $this->Dropdown->end();
                });

                echo $this->Html->ul($userMenu, ['class' => 'nav navbar-nav  navbar-right']);
            ?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>