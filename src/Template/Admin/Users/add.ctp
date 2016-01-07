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

<?php $this->assign('title', __d('me_cms', 'Add user')); ?>

<div class="users form">
	<?= $this->Html->h2(__d('me_cms', 'Add user')) ?>
    <?= $this->Form->create($user); ?>
	<div class='float-form'>
		<?php
			echo $this->Form->input('group_id', [
				'default'	=> config('users.default_group'),
				'label'		=> __d('me_cms', 'User group')
			]);
			echo $this->Form->input('active', [
				'checked'	=> TRUE,
				'label'		=> sprintf('%s?', __d('me_cms', 'Active')),
				'tip'		=> __d('me_cms', 'If is not active, the user won\'t be able to login')
			]);
		?>
	</div>
    <fieldset>
        <?php
			echo $this->Form->input('username', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Username')
			]);
			echo $this->Form->input('email', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Email')
			]);
			echo $this->Form->input('email_repeat', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Repeat email')
			]);
			echo $this->Form->input('password', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Password')
			]);
			echo $this->Form->input('password_repeat', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Repeat password')
			]);
			echo $this->Form->input('first_name', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'First name')
			]);
			echo $this->Form->input('last_name', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Last name')
			]);
        ?>
    </fieldset>
    <?= $this->Form->submit(__d('me_cms', 'Add user')) ?>
    <?= $this->Form->end() ?>
</div>