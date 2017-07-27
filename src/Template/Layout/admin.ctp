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
                '/vendor/font-awesome/css/font-awesome.min',
            ], ['block' => true]);
            echo $this->Asset->css([
                ME_CMS . '.admin/bootstrap.min',
                METOOLS . '.default',
                METOOLS . '.forms',
                ME_CMS . '.admin/layout',
                ME_CMS . '.admin/photos',
            ], ['block' => true]);
            echo $this->fetch('css');

            echo $this->Asset->script([
                '/vendor/jquery/jquery.min',
                '/vendor/js-cookie/js.cookie',
                ME_CMS . '.admin/bootstrap.min',
                METOOLS . '.default',
                ME_CMS . '.admin/layout',
                ME_CMS . '.display-password',
            ], ['block' => true]);
            echo $this->fetch('script');
        ?>
    </head>
    <body>
        <?php
        //Topbar is cached only if debugging is disabled
        $topbarCache = null;

        if (!getConfig('debug')) {
            $topbarCache = [
                'config' => 'admin',
                'key' => sprintf('topbar_user_%s', $this->Auth->user('id')),
            ];
        }

        echo $this->element(ME_CMS . '.admin/topbar', [], ['cache' => $topbarCache]);
        ?>
        <div class="container-fluid">
            <div class="row">
                <div id="sidebar" class="col-md-3 col-lg-2 hidden-xs hidden-sm affix-top">
                    <?php
                    //Sidebar is cached only if debugging is disabled
                    $sidebarCache = null;

                    if (!getConfig('debug')) {
                        $sidebarCache = [
                            'config' => 'admin',
                            'key' => sprintf('sidebar_user_%s', $this->Auth->user('id')),
                        ];
                    }

                    echo $this->element(ME_CMS . '.admin/sidebar', [], ['cache' => $sidebarCache]);
                    ?>
                </div>
                <div id="content" class="col-md-offset-3 col-lg-offset-2">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
        </div>
        <?= $this->fetch('css_bottom') ?>
        <?= $this->fetch('script_bottom') ?>
    </body>
</html>