<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */
use Cake\Core\Configure;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            echo $this->Html->charset();
            echo $this->Html->viewport();
            echo $this->Html->title($this->fetch('title'));
            echo $this->fetch('meta');

            echo $this->Html->css('https://fonts.googleapis.com/css?family=Roboto', ['block' => true]);
            echo $this->Asset->css([
                '/vendor/font-awesome/css/font-awesome.min',
                'MeCms.bootstrap.min',
                'MeTools.default',
                'MeTools.forms',
                'MeCms.userbar',
                'MeCms.cookies',
                'MeCms.widgets',
                'MeCms.layout',
                'MeCms.contents',
                'MeCms.photos'
            ], ['block' => true]);
            echo $this->fetch('css');

            echo $this->Asset->script([
                '/vendor/jquery/jquery.min',
                '/vendor/js-cookie/js.cookie',
                'MeCms.bootstrap.min',
                'MeTools.default',
                'MeCms.layout'
            ], ['block' => true]);
            echo $this->fetch('script');
        ?>
    </head>
    <body>
        <?= $this->element('MeCms.userbar') ?>
        <?= $this->element('MeCms.cookies_policy') ?>
        <header>
            <div class="container">
                <?php
                $logo = $this->Html->h1(config('main.title'));

                //Check if the logo image exists
                if (is_readable(WWW_ROOT . 'img' . DS . config('default.logo'))) {
                    $logo = $this->Html->img(config('default.logo'));
                }

                echo $this->Html->link($logo, '/', ['id' => 'logo', 'title' => __d('me_cms', 'Homepage')]);
                ?>
            </div>
            <?php
            //Topbar is cached only if debugging is disabled
            $topbarCache = null;

            if (!Configure::read('debug')) {
                $topbarCache = ['key' => 'topbar'];
            }

            echo $this->element('MeCms.topbar', [], ['cache' => $topbarCache]);
            ?>
        </header>
        <div class="container">
            <div class="row">
                <div id="content" class="col-sm-8 col-md-9">
                    <?php
                    echo $this->Flash->render();

                    if ($this->Breadcrumbs->render()) {
                        $this->Breadcrumbs->prepend(__d('me_cms', 'Home'), '/');
                        echo $this->Breadcrumbs->render();
                    }

                    echo $this->fetch('content');
                    ?>
                </div>
                <div id="sidebar" class="col-sm-4 col-md-3">
                    <?= $this->fetch('sidebar') ?>
                    <?= $this->Widget->all() ?>
                </div>
            </div>
        </div>
        <?php
        //Footer is cached only if debugging is disabled
        $footerCache = null;

        if (!Configure::read('debug')) {
            $footerCache = ['key' => 'footer'];
        }

        echo $this->element('MeCms.footer', [], ['cache' => $footerCache]);
        echo $this->fetch('css_bottom');
        echo $this->fetch('script_bottom');
        ?>
    </body>
</html>