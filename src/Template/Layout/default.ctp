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
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            echo $this->Html->charset();
            echo $this->Html->viewport();
            echo $this->Html->title($this->fetch('title'));
            echo $this->fetch('meta');

            echo $this->Html->css([
                'https://fonts.googleapis.com/css?family=Roboto|Abel',
                '/vendor/font-awesome/css/all',
            ], ['block' => true]);
            echo $this->Asset->css([
                '/vendor/bootstrap/css/bootstrap.min',
                ME_TOOLS . '.default',
                ME_TOOLS . '.forms',
                ME_CMS . '.userbar',
                ME_CMS . '.cookies',
                ME_CMS . '.layout',
                ME_CMS . '.contents',
            ], ['block' => true]);
            echo $this->fetch('css');

            echo $this->Asset->script([
                '/vendor/jquery/jquery.min',
                '/vendor/js-cookie/js.cookie',
                '/vendor/bootstrap/js/bootstrap.bundle.min',
                ME_TOOLS . '.default',
                ME_CMS . '.layout',
            ], ['block' => true]);
            echo $this->fetch('script');
        ?>
    </head>
    <body>
        <?= $this->element(ME_CMS . '.userbar') ?>
        <?= $this->element(ME_CMS . '.cookies_policy') ?>
        <header id="header">
            <div class="container">
                <?php
                $logo = $this->Html->h1(getConfigOrFail('main.title'));

                //Check if the logo image exists
                if (is_readable(WWW_ROOT . 'img' . DS . getConfig('default.logo'))) {
                    $logo = $this->Html->img(getConfig('default.logo'));
                }

                echo $this->Html->link($logo, '/', ['id' => 'logo', 'title' => __d('me_cms', 'Homepage')]);
                ?>
            </div>
            <?php
            //Topbar is cached only if debugging is disabled
            $topbarCache = null;

            if (!getConfig('debug')) {
                $topbarCache = ['key' => 'topbar'];
            }

            echo $this->element(ME_CMS . '.topbar', [], ['cache' => $topbarCache]);
            ?>
        </header>
        <div class="container mb-4">
            <div class="row">
                <main id="content" class="col-lg-9">
                    <?php
                    echo $this->Flash->render();

                    if ($this->Breadcrumbs->render()) {
                        $this->Breadcrumbs->prepend(__d('me_cms', 'Home'), '/');
                        echo $this->Breadcrumbs->render();
                    }

                    echo $this->fetch('content');
                    ?>
                </main>
                <nav id="sidebar" class="col">
                    <?= $this->fetch('sidebar') ?>
                    <?= $this->Widget->all() ?>
                </nav>
            </div>
        </div>
        <?php
        //Footer is cached only if debugging is disabled
        $footerCache = null;

        if (!getConfig('debug')) {
            $footerCache = ['key' => 'footer'];
        }

        echo $this->element(ME_CMS . '.footer', [], ['cache' => $footerCache]);
        echo $this->fetch('css_bottom');
        echo $this->fetch('script_bottom');
        ?>
    </body>
</html>