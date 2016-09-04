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

$this->extend('/Common/view');
$this->assign('title', __d('me_cms', 'Contact us'));
?>

<?= $this->Form->create($contact) ?>
<fieldset>
    <?php
        echo $this->Form->input('first_name', [
            'label' => __d('me_cms', 'First name'),
        ]);
        echo $this->Form->input('last_name', [
            'label' => __d('me_cms', 'Last name'),
        ]);
        echo $this->Form->input('email', [
            'autocomplete' => 'off',
            'label' => __d('me_cms', 'Email'),
            'tip' => __d('me_cms', 'Enter your email'),
        ]);
        echo $this->Form->input('message', [
            'label' => __d('me_cms', 'Message'),
            'rows' => 8,
            'type' => 'textarea',
        ]);

        if (config('security.recaptcha')) {
            echo $this->Recaptcha->recaptcha();
        }
    ?>
</fieldset>
<?= $this->Form->submit(__d('me_cms', 'Send'), ['class' => 'btn-block btn-lg btn-primary']) ?>
<?= $this->Form->end() ?>