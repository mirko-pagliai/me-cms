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
 *
 * @var \MeCms\View\View\AppView $this
 */

use Cake\Core\App;
use MeCms\Core\Plugin;

/**
 * The topbar element will use the `TopbarHelper` from APP to build links, if that helper exists. Otherwise, it will use
 *  the helper provided by MeCms, with the helper of any other plugin.
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

    <div class="container collapse navbar-collapse" id="topbarNav">
        <?php
        $app = (bool)App::className('TopbarHelper', 'View/Helper');
        $links = $this->loadHelper($app ? 'Topbar' : 'MeCms.Topbar')->build();
        $this->helpers()->unload('Topbar');

        if (!$app) {
            //Builds links with any other plugin helper
            foreach (Plugin::all(['core' => false, 'exclude' => ['MeCms']]) as $plugin) {
                if (App::className($plugin . '.TopbarHelper', 'View/Helper')) {
                    $links = [...$links, ...$this->loadHelper($plugin . '.Topbar')->build()];
                    $this->helpers()->unload('Topbar');
                }
            }
        }

        echo $this->Html->ul($links, ['class' => 'navbar-nav mr-auto'], ['class' => 'nav-item mr-3']);
        ?>
    </div>
</nav>
