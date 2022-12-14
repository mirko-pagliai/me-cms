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
$app = (bool)App::className('TopbarHelper', 'View/Helper');
/** @var \MeCms\View\Helper\TopbarHelper $TopbarHelper */
$TopbarHelper = $this->loadHelper($app ? 'Topbar' : 'MeCms.Topbar');
$links = $TopbarHelper->build();
$this->helpers()->unload('Topbar');

if (!$app) {
    //Builds links with any other plugin helper
    foreach (Plugin::all(['core' => false, 'exclude' => ['MeCms']]) as $plugin) {
        if (App::className($plugin . '.TopbarHelper', 'View/Helper')) {
            /** @var \MeCms\View\Helper\TopbarHelper $TopbarHelper */
            $TopbarHelper = $this->loadHelper($plugin . '.Topbar');
            $links = [...$links, ...$TopbarHelper->build()];
            $this->helpers()->unload('Topbar');
        }
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse d-none d-lg-block">
            <ul class="navbar-nav me-auto gap-3">
                <?php foreach ($links as $link) : ?>
                    <li class="nav-item fs-5">
                        <?= $link ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="offcanvasNavbar">
        <div class="offcanvas-header">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<?= __d('me_cms', 'Close') ?>"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav flex-grow-1 pe-3">
                <?php foreach ($links as $link) : ?>
                    <li class="nav-item fs-5">
                        <?= $link ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
