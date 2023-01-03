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
 * @var \MeCms\View\View\AdminView $this
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
            'MeCms.admin/layout',
        ], ['block' => true]);
        echo $this->fetch('css');

        echo $this->Asset->script([
            '/vendor/jquery/jquery.min',
            '/vendor/bootstrap/js/bootstrap.bundle.min',
            'MeCms.js.cookie.min',
            'MeTools.default',
            'MeCms.admin/layout',
            'MeCms.display-password',
        ], ['block' => true]);
        echo $this->fetch('script');
        ?>
    </head>
    <body>
        <?= $this->element('MeCms.admin/userbar') ?>
        <div class="container-fluid">
            <div class="row">
                <nav id="sidebar" class="col d-none d-lg-block border-end min-vh-100 py-4 p-3">
                    <?= $this->element('MeCms.admin/sidebar', [], getConfig('debug') ? [] : ['cache' => [
                        'config' => 'admin',
                        'key' => 'sidebar_user_' . $this->Identity->get('id'),
                    ]]) ?>
                </nav>
                <main class="col-lg-10 py-4 p-3">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </main>
            </div>
        </div>
        <?= $this->fetch('css_bottom') ?>
        <?= $this->fetch('script_bottom') ?>
    </body>
</html>
