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
                $menus = [];

                $menus[] = $this->Html->link(
                    __d('me_cms', 'Homepage'),
                    ['_name' => 'homepage'],
                    ['icon' => 'home', 'target' => '_blank']
                );

                echo $this->Html->ul($menus, ['class' => 'nav navbar-nav hidden-xs hidden-sm']);

                //Renders menus for each plugin
                foreach (Plugin::all(['exclude' => [METOOLS, ASSETS, DATABASE_BACKUP, THUMBER]]) as $plugin) {
                    $menus += $this->MenuBuilder->renderAsDropdown($plugin);
                }

                echo $this->Html->ul($menus, ['class' => 'nav navbar-nav visible-xs visible-sm']);

                $userMenu[] = call_user_func(function () {
                    $this->Dropdown->start($this->Auth->user('full_name'), ['icon' => 'user']);

                    if (getConfig('users.login_log')) {
                        echo $this->Html->link(__d('me_cms', 'Last login'), [
                            'controller' => 'Users',
                            'action' => 'lastLogin',
                            'plugin' => ME_CMS,
                        ]);
                    }

                    echo $this->Html->link(__d('me_cms', 'Change password'), [
                        'controller' => 'Users',
                        'action' => 'changePassword',
                        'plugin' => ME_CMS,
                    ]);
                    echo $this->Html->link(__d('me_cms', 'Logout'), ['_name' => 'logout']);

                    return $this->Dropdown->end();
                });

                echo $this->Html->ul($userMenu, ['class' => 'nav navbar-nav  navbar-right']);
            ?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>