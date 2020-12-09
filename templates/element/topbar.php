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

use Cake\Core\App;
use MeCms\Core\Plugin;

/**
 * The topbar element will use the `TopbarHelper` from APP to build links, if
 *  that helper exists. Otherwise it will use the helper provided by MeCms, with
 *  the helper of any other plugin.
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
        if (App::className('TopbarHelper', 'View/Helper')) {
            //Builds links with the APP helper
            $links = $this->loadHelper('Topbar')->build();
            $this->helpers()->unload('Topbar');
        } else {
            //Builds links with the MeCms helper
            $links = $this->loadHelper('MeCms.Topbar')->build();
            $this->helpers()->unload('Topbar');

            //Builds links with any other plugin helper
            foreach (Plugin::all(['core' => false, 'exclude' => ['MeCms']]) as $plugin) {
                if (App::className($plugin . '.TopbarHelper', 'View/Helper')) {
                    $links = array_merge($links, $this->loadHelper($plugin . '.Topbar')->build());
                    $this->helpers()->unload('Topbar');
                }
            }
        }

        echo $this->Html->ul($links, ['class' => 'container navbar-nav mr-auto'], ['class' => 'nav-item']);
        ?>
    </div>
</nav>
