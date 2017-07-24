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
$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Edit user'));
?>

<?= $this->Form->create($user); ?>
<div class='float-form'>
    <?= $this->Form->control('group_id', [
        'label' => __d('me_cms', 'User group')
    ]) ?>
</div>
<fieldset>
    <?php
        echo $this->Form->control('username', [
            'disabled' => true,
            'label' => __d('me_cms', 'Username'),
        ]);
        echo $this->Form->control('email', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Email'),
        ]);
        echo $this->Form->control('password', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => __d('me_cms', 'Show/hide password'),
             ]),
            'help' => __d('me_cms', 'If you want to change the password just ' .
                'type a new one. Otherwise, leave the field empty'),
            'label' => __d('me_cms', 'Password'),
            'required' => false,
            'value' => '',
        ]);
        echo $this->Form->control('password_repeat', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => __d('me_cms', 'Show/hide password'),
             ]),
            'help' => __d('me_cms', 'If you want to change the password just ' .
                'type a new one. Otherwise, leave the field empty'),
            'label' => __d('me_cms', 'Repeat password'),
            'required' => false,
            'value' => '',
        ]);
        echo $this->Form->control('first_name', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'First name'),
        ]);
        echo $this->Form->control('last_name', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Last name'),
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>