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
 * @var \MeCms\View\View\AdminView $this
 * @var \MeCms\Model\Entity\User $user
 */
$this->extend('MeCms./common/form');
$this->assign('title', $title = __d('me_cms', 'Edit user'));
?>

<?= $this->Form->create($user); ?>
<div class="row">
    <div class="col-lg-3 order-last">
        <div class="float-form">
        <?= $this->Form->control('group_id', ['label' => __d('me_cms', 'User group')]) ?>
        </div>
    </div>
    <fieldset class="col">
    <?php
    echo $this->Form->control('username', [
        'disabled' => true,
        'label' => I18N_USERNAME,
    ]);
    echo $this->Form->control('email', [
        'autocomplete' => 'off',
        'label' => I18N_EMAIL,
    ]);
    echo $this->Form->control('password', [
        'append-text' => $this->Html->button('', '#', [
            'class' => 'display-password',
            'icon' => 'eye',
            'title' => I18N_SHOW_HIDE_PASSWORD,
         ]),
        'autocomplete' => 'off',
        'help' => __d('me_cms', 'If you want to change the password just ' .
            'type a new one. Otherwise, leave the field empty'),
        'label' => I18N_PASSWORD,
        'required' => false,
        'value' => '',
    ]);
    echo $this->Form->control('password_repeat', [
        'append-text' => $this->Html->button('', '#', [
            'class' => 'display-password',
            'icon' => 'eye',
            'title' => I18N_SHOW_HIDE_PASSWORD,
         ]),
        'autocomplete' => 'off',
        'help' => __d('me_cms', 'If you want to change the password just ' .
            'type a new one. Otherwise, leave the field empty'),
        'label' => I18N_REPEAT_PASSWORD,
        'required' => false,
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
</div>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>
