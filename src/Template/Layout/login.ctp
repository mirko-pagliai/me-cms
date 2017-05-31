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
                'MeCms.login/bootstrap.min',
                'MeTools.default',
                'MeTools.forms',
                'MeCms.login/layout'
            ], ['block' => true]);
            echo $this->fetch('css');

            echo $this->Asset->script([
                '/vendor/jquery/jquery.min',
                'MeCms.display-password',
            ], ['block' => true]);
            echo $this->fetch('script');
        ?>
    </head>
    <body>
        <div id="content" class="container">
            <?php
            $logo = $this->Html->h1(getConfig('main.title'), ['id' => 'logo']);

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