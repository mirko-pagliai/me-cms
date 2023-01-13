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

$this->assign('title', $title = __d('me_cms', 'Login'));
?>

<div id="login">
    <?= $this->Form->create(null, ['validation' => false]) ?>
    <fieldset>
        <?php
        echo $this->Form->control('username', [
            'autofocus' => true,
            'label' => false,
            'placeholder' => I18N_USERNAME,
        ]);
        echo $this->Form->control('password', [
            'append-text' => $this->Html->link('', '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => I18N_SHOW_HIDE_PASSWORD,
             ]),
            'label' => false,
            'placeholder' => I18N_PASSWORD,
        ]);
        echo $this->Form->control('remember_me', [
            'help' => __d('me_cms', 'Don\'t use on public computers'),
            'label' => __d('me_cms', 'Remember me'),
            'type' => 'checkbox',
        ]);
        ?>
    </fieldset>
    <?= $this->Form->submit($title, ['class' => 'btn-block btn-lg btn-primary']) ?>
    <?= $this->Form->end() ?>

    <?= $this->element('login/menu'); ?>
</div>
