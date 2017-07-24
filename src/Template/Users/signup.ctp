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
$this->assign('title', $title = __d('me_cms', 'Sign up'));
?>

<?= $this->Form->create($user); ?>
<fieldset>
    <?php
        echo $this->Form->control('username', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Username'),
        ]);
        echo $this->Form->control('email', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Email'),
            'help' => __d('me_cms', 'Enter your email'),
        ]);
        echo $this->Form->control('email_repeat', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Repeat email'),
            'help' => __d('me_cms', 'Repeat your email'),
        ]);
        echo $this->Form->control('password', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => __d('me_cms', 'Show/hide password'),
             ]),
            'label' => __d('me_cms', 'Password'),
            'help' => __d('me_cms', 'Enter your password'),
        ]);
        echo $this->Form->control('password_repeat', [
            'autocomplete' => 'off',
            'button' => $this->Html->button(null, '#', [
                'class' => 'display-password',
                'icon' => 'eye',
                'title' => __d('me_cms', 'Show/hide password'),
             ]),
            'label' => __d('me_cms', 'Repeat password'),
            'help' => __d('me_cms', 'Repeat your password'),
        ]);
        echo $this->Form->control('first_name', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'First name'),
        ]);
        echo $this->Form->control('last_name', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Last name'),
        ]);

        if (getConfig('security.recaptcha')) {
            echo $this->Recaptcha->display();
        }
    ?>
</fieldset>
<?= $this->Form->submit($title, ['class' => 'btn-block btn-lg btn-primary']) ?>
<?= $this->Form->end() ?>

<?= $this->element('login/menu'); ?>