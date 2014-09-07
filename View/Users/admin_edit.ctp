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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Users
 */
?>

<?php $this->assign('sidebar', $this->Menu->get('users', 'nav')); ?>

<div class="users form">
	<?php echo $this->Html->h2(__d('me_cms', 'Edit user')); ?>
	<?php echo $this->Form->create('User', array('class' => 'form-base')); ?>
		<div class='float-form'>
			<?php
				echo $this->Form->input('group_id');
			?>
		</div>
		<fieldset>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('username');
				echo $this->Form->input('email');
				echo $this->Form->input('password', array(
					'tip' => __d('me_cms', 'If you want to change the password just type a new one. Otherwise, leave the field empty')
				));
				echo $this->Form->input('password_repeat', array(
					'label'	=> __d('me_cms', 'Repeat password'),
					'type'	=> 'password'
				));
				echo $this->Form->input('first_name');
				echo $this->Form->input('last_name');
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Edit user')); ?>
</div>