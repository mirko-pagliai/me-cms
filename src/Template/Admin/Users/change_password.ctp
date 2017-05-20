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
$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Change your password'));
?>

<?= $this->Form->create($user) ?>
    <fieldset>
        <?php
            echo $this->Form->control('password_old', [
                'autocomplete' => 'off',
                'button' => $this->Html->button(null, '#', [
                    'class' => 'display-password',
                    'icon' => 'eye',
                    'title' => __d('me_cms', 'Show/hide password'),
                 ]),
                'help' => __d('me_cms', 'Enter your old password'),
                'label' => __d('me_cms', 'Old password'),
                'type' => 'password',
                'value' => '',
            ]);
            echo $this->Form->control('password', [
                'autocomplete' => 'off',
                'button' => $this->Html->button(null, '#', [
                    'class' => 'display-password',
                    'icon' => 'eye',
                    'title' => __d('me_cms', 'Show/hide password'),
                 ]),
                'help' => __d('me_cms', 'Enter your new password'),
                'label' => __d('me_cms', 'Password'),
                'value' => '',
            ]);
            echo $this->Form->control('password_repeat', [
                'autocomplete' => 'off',
                'button' => $this->Html->button(null, '#', [
                    'class' => 'display-password',
                    'icon' => 'eye',
                    'title' => __d('me_cms', 'Show/hide password'),
                 ]),
                'help' => __d('me_cms', 'Repeat your new password'),
                'label' => __d('me_cms', 'Repeat password'),
                'value' => '',
            ]);
        ?>
    </fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>