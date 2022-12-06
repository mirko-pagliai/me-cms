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

use Cake\Core\Configure;

$sidebar = $this->fetch('sidebar') . $this->Widget->all();
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

        //Default css and css files from the application (`layout.css` and `contents.css`)
        $css = array_filter(['layout', 'contents'], fn(string $name): bool => is_readable(Configure::read('App.wwwRoot') . Configure::read('App.cssBaseUrl') . $name . '.css'));
        echo $this->Asset->css([
            '/vendor/bootstrap/css/bootstrap.min',
            'MeTools.default',
            'MeTools.forms',
            'MeCms.cookies',
            'MeCms.layout',
            'MeCms.contents',
            ...$css,
        ], ['block' => true]);

        //Other css files
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
    <body class="d-flex flex-column min-vh-100">
        <?= $this->element('MeCms.userbar') ?>
        <?= $this->element('MeCms.cookies_policy') ?>
        <header class="container">
            <?php
            $logo = $this->Html->h1(getConfigOrFail('main.title'));
            if (is_readable(WWW_ROOT . 'img' . DS . getConfig('default.logo'))) {
                $logo = $this->Html->image(getConfig('default.logo'));
            }
            echo $this->Html->link($logo, '/', ['class' => 'd-block my-5 text-center', 'title' => __d('me_cms', 'Homepage')]);

            echo $this->element('MeCms.topbar', [], getConfig('debug') ? [] : ['cache' => ['key' => 'topbar']]);
            ?>
        </header>
        <div class="container flex-grow-1 my-5">
            <div class="row">
                <main class="col">
                    <?php
                    echo $this->Flash->render();

                    if ($this->Breadcrumbs->render()) {
                        $this->Breadcrumbs->prepend(__d('me_cms', 'Home'), '/');
                        echo $this->Breadcrumbs->render();
                    }

                    echo $this->fetch('content');
                    ?>
                </main>
            <?php if ($sidebar) : ?>
                <nav id="sidebar" class="col-lg-3">
                    <?= $this->fetch('sidebar') ?>
                    <?= $this->Widget->all() ?>
                </nav>
            <?php endif; ?>
            </div>
        </div>
        <footer class="p-4 small text-center">
            <?= $this->element('MeCms.footer', [], getConfig('debug') ? [] : ['cache' => ['key' => 'footer']]) ?>
        </footer>
        <?= $this->fetch('css_bottom') ?>
        <?= $this->fetch('script_bottom') ?>
    </body>
</html>
