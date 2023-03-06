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

use Cake\I18n\I18n;
?>
<!DOCTYPE html>
<html lang="<?= substr(I18n::getLocale(), 0, 2) ?>">
    <head>
        <?php
        echo $this->Html->charset();
        echo $this->Html->viewport();
        echo $this->Html->title($this->fetch('title'));
        echo $this->fetch('meta');

        echo $this->Asset->css([
            '/vendor/font-awesome/css/all.min',
            'MeCms.fonts',
            '/vendor/bootstrap/css/bootstrap.min',
            'MeTools.default',
            'MeTools.forms',
            'MeCms.single-column/layout',
        ], ['block' => true]);
        echo $this->fetch('css');

        echo $this->Asset->script([
            '/vendor/jquery/jquery.min',
            '/vendor/bootstrap/js/bootstrap.bundle.min',
            'MeCms.display-password',
        ], ['block' => true]);
        echo $this->fetch('script');
        ?>
    </head>
    <body>
        <main class="container col-sm-7 col-md-6 col-lg-5 col-xl-3 my-sm-5 p-4 rounded rounded-sm-4">
            <?php
            $logoOptions = ['id' => 'logo', 'class' => 'd-block m-auto mt-2 mb-5 text-center text-truncate'];
            $logo = $this->Html->h1(getConfigOrFail('main.title'), $logoOptions);
            //First checks if the `logo_login.png` file exists, otherwise it uses the default logo
            if (is_readable(WWW_ROOT . 'img' . DS . 'logo_login.png')) {
                $logo = $this->Html->image('logo_login.png', $logoOptions);
            } elseif (is_readable(WWW_ROOT . 'img' . DS . getConfig('default.logo'))) {
                $logo = $this->Html->image(getConfig('default.logo'), $logoOptions);
            }
            echo $logo;

            echo $this->Flash->render();
            echo $this->Flash->render('auth');
            echo $this->fetch('content');
            ?>
        </main>
        <?= $this->fetch('css_bottom') ?>
        <?= $this->fetch('script_bottom') ?>
    </body>
</html>
