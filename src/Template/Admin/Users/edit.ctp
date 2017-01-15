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
$this->assign('title', $title = __d('me_cms', 'Edit user'));
?>

<?= $this->Form->create($user); ?>
<div class='float-form'>
    <?php
        echo $this->Form->input('group_id', [
            'label' => __d('me_cms', 'User group')
        ]);
    ?>
</div>
<fieldset>
    <?php
        echo $this->Form->input('username', [
            'disabled' => true,
            'label' => __d('me_cms', 'Username'),
        ]);
        echo $this->Form->input('email', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Email'),
        ]);
        echo $this->Form->input('password', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => __d('me_cms', 'Show/hide password'),
             ]),
            'label' => __d('me_cms', 'Password'),
            'required' => false,
            'help' => __d('me_cms', 'If you want to change the password just ' .
                'type a new one. Otherwise, leave the field empty'),
        ]);
        echo $this->Form->input('password_repeat', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => __d('me_cms', 'Show/hide password'),
             ]),
            'label' => __d('me_cms', 'Repeat password'),
            'required' => false,
            'help' => __d('me_cms', 'If you want to change the password just ' .
                'type a new one. Otherwise, leave the field empty'),
        ]);
        echo $this->Form->input('first_name', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'First name'),
        ]);
        echo $this->Form->input('last_name', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Last name'),
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>