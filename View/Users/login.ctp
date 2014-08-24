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
	
<div class="login form">
	<?php
		echo $this->Session->flash();
		echo $this->Session->flash('auth');
	?>
	<?php echo $this->Form->create('User', array('class' => 'form-base')); ?>
		<fieldset>
			<?php
				echo $this->Form->input('username', array(
					'autofocus'		=> TRUE,
					'label'			=> FALSE,
					'placeholder'	=> __d('me_cms_backend', 'Username')
				));
				echo $this->Form->input('password', array(
					'label'			=> FALSE,
					'placeholder'	=> __d('me_cms_backend', 'Password')
				));
				echo $this->Form->submit(__d('me_cms_backend', 'Login'), array('class' => 'btn-primary btn-lg btn-block'));
			?>
		</fieldset>
	<?php echo $this->Form->end(); ?>
</div>