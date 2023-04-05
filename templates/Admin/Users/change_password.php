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
 * @var \MeCms\View\View\Admin\AppView $this
 * @var string $title
 * @var \MeCms\Model\Entity\User $user
 */

$this->extend('MeCms./common/form');
$this->assign('title', $title = __d('me_cms', 'Change your password'));
?>

<?= $this->Form->create($user) ?>
<fieldset>
    <?php
    echo $this->Form->control('password_old', [
        'append-text' => $this->Html->link('', '#', [
            'class' => 'display-password',
            'icon' => 'eye',
            'title' => I18N_SHOW_HIDE_PASSWORD,
        ]),
        'autocomplete' => 'off',
        'help' => __d('me_cms', 'Enter your old password'),
        'label' => __d('me_cms', 'Old password'),
        'type' => 'password',
        'value' => '',
    ]);
    echo $this->Form->control('password', [
        'append-text' => $this->Html->link('', '#', [
            'class' => 'display-password',
            'icon' => 'eye',
            'title' => I18N_SHOW_HIDE_PASSWORD,
        ]),
        'autocomplete' => 'off',
        'help' => __d('me_cms', 'Enter your new password'),
        'label' => I18N_PASSWORD,
        'value' => '',
    ]);
    echo $this->Form->control('password_repeat', [
        'append-text' => $this->Html->link('', '#', [
            'class' => 'display-password',
            'icon' => 'eye',
            'title' => I18N_SHOW_HIDE_PASSWORD,
        ]),
        'autocomplete' => 'off',
        'help' => __d('me_cms', 'Repeat your new password'),
        'label' => I18N_REPEAT_PASSWORD,
        'value' => '',
    ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>
