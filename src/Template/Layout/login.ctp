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
                ME_CMS . '.login/layout'
            ], ['block' => true]);
            echo $this->fetch('css');

            echo $this->Asset->script([
                '/vendor/jquery/jquery.min',
                ME_CMS . '.display-password',
            ], ['block' => true]);
            echo $this->fetch('script');
        ?>
    </head>
    <body>
        <div id="content" class="container">
            <?php
            $logo = $this->Html->h1(getConfigOrFail('main.title'), ['id' => 'logo']);

            //Check if the logo image exists
            if (is_readable(WWW_ROOT . 'img' . DS . getConfig('default.logo'))) {
                $logo = $this->Html->img(getConfig('default.logo'), ['id' => 'logo']);
            }

            echo $logo;
            echo $this->Flash->render();
            echo $this->Flash->render('auth');
            echo $this->fetch('content');
            ?>
        </div>
        <?= $this->fetch('css_bottom') ?>
        <?= $this->fetch('script_bottom') ?>
    </body>
</html>