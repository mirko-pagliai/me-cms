<?php
/**
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\View\Users
 */
?>
	
<div class="users form">
	<?php echo $this->Html->h2(__d('me_cms_backend', 'Change password')); ?>
	<?php echo $this->Form->create('User', array('class' => 'form-base')); ?>
		<fieldset>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('old_password', array(
					'label'	=> __d('me_cms_backend', 'Old password'),
					'tip'	=> __d('me_cms_backend', 'Enter your old password'),
					'type'	=> 'password'
				));
				echo $this->Form->input('password', array(
					'tip' => __d('me_cms_backend', 'Enter your new password')
				));
				echo $this->Form->input('password_repeat', array(
					'label'	=> __d('me_cms_backend', 'Repeat password'),
					'tip'	=> __d('me_cms_backend', 'Repeat your new password'),
					'type'	=> 'password'
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms_backend', 'Change password')); ?>
</div>