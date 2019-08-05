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

<nav id="topbar" class="navbar navbar-expand-lg navbar-dark bg-dark">
    <?= $this->Html->button($this->Html->span(null, ['class' => 'navbar-toggler-icon']), null, [
        'class' => 'navbar-toggler',
        'data-toggle' => 'collapse',
        'data-target' => '#topbarNav',
        'aria-controls' => 'topbarNav',
        'aria-expanded' => 'false',
        'aria-label' => __d('me_cms', 'Toggle navigation'),
    ]) ?>

    <div class="collapse navbar-collapse" id="topbarNav">
        <?php
        $links = [
            $this->Html->link(__d('me_cms', 'Home'), ['_name' => 'homepage'], ['class' => 'nav-link', 'icon' => 'home']),
            $this->Html->link(__d('me_cms', 'Categories'), ['_name' => 'postsCategories'], ['class' => 'nav-link']),
            $this->Html->link(I18N_PAGES, ['_name' => 'pagesCategories'], ['class' => 'nav-link']),
            $this->Html->link(I18N_PHOTOS, ['_name' => 'albums'], ['class' => 'nav-link']),
        ];

        echo $this->Html->ul($links, ['class' => 'container navbar-nav mr-auto'], ['class' => 'nav-item']);
        ?>
    </div>
</nav>
