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
            '/vendor/font-awesome/css/all.min',
        ], ['block' => true]);
        echo $this->Asset->css([
            '/vendor/bootstrap/css/bootstrap.min',
            'MeTools.default',
            'MeTools.forms',
            'MeCms.cookies',
            'MeCms.layout',
            'MeCms.contents',
        ], ['block' => true]);
        echo $this->Asset->css('MeCms.print', ['block' => true, 'media' => 'print']);
        echo $this->fetch('css');

        echo $this->Asset->script([
            '/vendor/jquery/jquery.min',
            '/vendor/bootstrap/js/bootstrap.bundle.min',
            'MeCms.js.cookie.min',
            'MeTools.default',
            'MeCms.layout',
        ], ['block' => true]);
        echo $this->fetch('script');
        ?>
    </head>
    <body>
        <?= $this->element('MeCms.userbar') ?>
        <?= $this->element('MeCms.cookies_policy') ?>
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
            <?= $this->element('MeCms.topbar', [], ['cache' => getConfig('debug') ? null : ['key' => 'topbar']]) ?>
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
        echo $this->element('MeCms.footer', [], ['cache' => getConfig('debug') ? null : ['key' => 'footer']]);
        echo $this->fetch('css_bottom');
        echo $this->fetch('script_bottom');
        ?>
    </body>
</html>
