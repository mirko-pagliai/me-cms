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
$this->assign('title', $title = __d('me_cms', 'Add user'));
?>

<?= $this->Form->create($user); ?>
<div class='float-form'>
    <?php
        echo $this->Form->control('group_id', [
            'default' => getConfigOrFail('users.default_group'),
            'label' => __d('me_cms', 'User group'),
        ]);
        echo $this->Form->control('active', [
            'checked' => true,
            'help' => __d('me_cms', 'If is not active, the user won\'t be able to login'),
            'label' => sprintf('%s?', __d('me_cms', 'Active')),
        ]);
    ?>
</div>
<fieldset>
    <?php
        echo $this->Form->control('username', [
            'autocomplete' => 'off',
            'label' => I18N_USERNAME,
        ]);
        echo $this->Form->control('email', [
            'autocomplete' => 'off',
            'label' => I18N_EMAIL,
        ]);
        echo $this->Form->control('email_repeat', [
            'autocomplete' => 'off',
            'label' => I18N_REPEAT_EMAIL,
        ]);
        echo $this->Form->control('password', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => I18N_SHOW_HIDE_PASSWORD,
             ]),
            'label' => I18N_PASSWORD,
            'value' => '',
        ]);
        echo $this->Form->control('password_repeat', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => I18N_SHOW_HIDE_PASSWORD,
             ]),
            'label' => I18N_REPEAT_PASSWORD,
            'value' => '',
        ]);
        echo $this->Form->control('first_name', [
            'autocomplete' => 'off',
            'label' => I18N_FIRST_NAME,
        ]);
        echo $this->Form->control('last_name', [
            'autocomplete' => 'off',
            'label' => I18N_LAST_NAME,
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>