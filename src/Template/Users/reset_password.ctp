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
 * @package		MeCms\View\Profiles
 */
?>

<?php $this->assign('title', __d('me_cms', 'Reset password')); ?>

<div class="users form">
	<?= $this->Html->h2(__d('me_cms', 'Reset password')) ?>
	<?= $this->Form->create($user) ?>
	<fieldset>
		<?php
			echo $this->Form->input('password', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Password'),
				'tip'			=> __d('me_cms', 'Enter your new password')
			]);
			echo $this->Form->input('password_repeat', [
				'autocomplete'	=> FALSE,
				'label'			=> __d('me_cms', 'Repeat password'),
				'tip'			=> __d('me_cms', 'Repeat your new password')
			]);
		?>
	</fieldset>
	<?= $this->Form->submit(__d('me_cms', 'Reset password'), array('class' => 'btn-block btn-lg btn-primary')) ?>
	<?= $this->Form->end() ?>
	<?= $this->element('login/menu'); ?>
</div>