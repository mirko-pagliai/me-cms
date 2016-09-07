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

$this->extend('/Common/form');
$this->assign('title', $title = __d('me_cms', 'Login'));
?>

<div id="login">
    <?= $this->Form->create('User') ?>
    <fieldset>
        <?php
            echo $this->Form->input('username', [
                'autofocus' => true,
                'label' => false,
                'placeholder' => __d('me_cms', 'Username'),
            ]);
            echo $this->Form->input('password', [
                'button' => $this->Html->button(null, '#', [
                    'class' => 'display-password',
                    'icon' => 'eye',
                    'title' => __d('me_cms', 'Show/hide password'),
                 ]),
                'label' => false,
                'placeholder' => __d('me_cms', 'Password'),
            ]);
            echo $this->Form->input('remember_me', [
                'label' => __d('me_cms', 'Remember me'),
                'tip' => __d('me_cms', 'Don\'t use on public computers'),
                'type' => 'checkbox',
            ]);
        ?>
    </fieldset>
    <?= $this->Form->submit($title, ['class' => 'btn-block btn-lg btn-primary']) ?>
    <?= $this->Form->end() ?>

    <?= $this->element('login/menu'); ?>
</div>