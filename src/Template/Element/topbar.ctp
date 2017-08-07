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
?>
<nav id="topbar" class="navbar navbar-default" role="navigation">
    <div class="container">
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
            <?= $this->Html->ul([
                $this->Html->link(__d('me_cms', 'Home'), ['_name' => 'homepage'], ['icon' => 'home']),
                $this->Html->link(__d('me_cms', 'Categories'), ['_name' => 'postsCategories']),
                $this->Html->link(I18N_PAGES, ['_name' => 'pagesCategories']),
                $this->Html->link(I18N_PHOTOS, ['_name' => 'albums']),
            ], ['class' => 'nav navbar-nav']) ?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>