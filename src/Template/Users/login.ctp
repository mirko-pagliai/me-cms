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
$this->extend('/Common/form');
$this->assign('title', $title = __d('me_cms', 'Login'));
?>

<div id="login">
    <?= $this->Form->create('User') ?>
    <fieldset>
        <?php
            echo $this->Form->control('username', [
                'autofocus' => true,
                'label' => false,
                'placeholder' => __d('me_cms', 'Username'),
            ]);
            echo $this->Form->control('password', [
                'button' => $this->Html->button(null, '#', [
                    'class' => 'display-password',
                    'icon' => 'eye',
                    'title' => __d('me_cms', 'Show/hide password'),
                 ]),
                'label' => false,
                'placeholder' => __d('me_cms', 'Password'),
            ]);
            echo $this->Form->control('remember_me', [
                'label' => __d('me_cms', 'Remember me'),
                'help' => __d('me_cms', 'Don\'t use on public computers'),
                'type' => 'checkbox',
            ]);
        ?>
    </fieldset>
    <?= $this->Form->submit($title, ['class' => 'btn-block btn-lg btn-primary']) ?>
    <?= $this->Form->end() ?>

    <?= $this->element('login/menu'); ?>
</div>