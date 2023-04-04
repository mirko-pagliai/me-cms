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
 * @var \MeCms\Form\ContactUsForm $contact
 * @var \MeCms\View\View\AppView $this
 */
$this->extend('/common/view');
$this->assign('title', __d('me_cms', 'Contact us'));
?>

<?= $this->Form->create($contact) ?>
<fieldset>
    <?php
    echo $this->Form->control('first_name', [
        'label' => I18N_FIRST_NAME,
    ]);
    echo $this->Form->control('last_name', [
        'label' => I18N_LAST_NAME,
    ]);
    echo $this->Form->control('email', [
        'autocomplete' => 'off',
        'help' => I18N_ENTER_YOUR_EMAIL,
        'label' => I18N_EMAIL,
    ]);
    echo $this->Form->control('message', [
        'label' => __d('me_cms', 'Message'),
        'rows' => 8,
        'type' => 'textarea',
    ]);

    if (getConfig('security.recaptcha')) {
        echo $this->Recaptcha->display();
    }
    ?>
</fieldset>
<?= $this->Form->submit(__d('me_cms', 'Send'), ['class' => 'btn-block btn-lg btn-primary']) ?>
<?= $this->Form->end() ?>
