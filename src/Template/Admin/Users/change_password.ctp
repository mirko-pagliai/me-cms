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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
    $this->extend('/Admin/Common/form');
    $this->assign('title', $title = __d('me_cms', 'Change your password'));
?>

<?= $this->Form->create($user) ?>
    <fieldset>
        <?php
            echo $this->Form->input('password_old', [
                'autocomplete' => FALSE,
                'label' => __d('me_cms', 'Old password'),
                'tip' => __d('me_cms', 'Enter your old password'),
                'type' => 'password',
            ]);
            echo $this->Form->input('password', [
                'autocomplete' => FALSE,
                'label'	=> __d('me_cms', 'Password'),
                'tip' => __d('me_cms', 'Enter your new password'),
            ]);
            echo $this->Form->input('password_repeat', [
                'autocomplete' => FALSE,
                'label' => __d('me_cms', 'Repeat password'),
                'tip' => __d('me_cms', 'Repeat your new password'),
            ]);
        ?>
    </fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>