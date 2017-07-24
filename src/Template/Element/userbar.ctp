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

if (!getConfig('users.userbar')) {
    return;
}

if (!$this->Auth->isLogged()) {
    return;
}
?>

<nav id="userbar" class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#userbar-collapse">
                <span class="sr-only"><?= __d('me_cms', 'Toggle navigation') ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="userbar-collapse">
            <?php
                echo $this->Html->ul([
                    $this->Html->link(__d('me_cms', 'Dashboard'), ['_name' => 'dashboard'], ['icon' => 'dashboard']),
                    $this->fetch('userbar'),
                ], ['class' => 'nav navbar-nav']);

                echo $this->Html->ul([
                    $this->Dropdown->menu($this->Auth->user('full_name'), [
                        $this->Html->link(__d('me_cms', 'Last login'), [
                            'controller' => 'Users',
                            'action' => 'lastLogin',
                            'plugin' => ME_CMS,
                        ]),
                        $this->Html->link(__d('me_cms', 'Change password'), [
                            'controller' => 'Users',
                            'action' => 'changePassword',
                            'plugin' => ME_CMS,
                        ]),
                        $this->Html->link(__d('me_cms', 'Logout'), ['_name' => 'logout']),
                    ], ['icon' => 'user']),
                ], ['class' => 'nav navbar-nav navbar-right']);
            ?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>