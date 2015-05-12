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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Users
 */
?>
	
<div id="login" class="users form">
	<?php echo $this->Form->create('User'); ?>
		<fieldset>
			<?php
				echo $this->Form->input('username', array(
					'autofocus'		=> TRUE,
					'label'			=> FALSE,
					'placeholder'	=> __d('me_cms', 'Username')
				));
				echo $this->Form->input('password', array(
					'label'			=> FALSE,
					'placeholder'	=> __d('me_cms', 'Password')
				));
				echo $this->Form->input('remember_me', array(
					'label'	=> __d('me_cms', 'Remember me'),
					'tip'	=> __d('me_cms', 'Don\'t use on public computers'),
					'type'	=> 'checkbox'
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Login'), array('class' => 'btn-block btn-lg btn-primary')); ?>
	<?php
		$menu = array();
		
		//If signup is enabled
		if($config['users']['signup'])
			$menu[] = $this->Html->link(__d('me_cms', 'Sign up'), array('controller' => 'profiles', 'action' => 'signup'));
		
		//If signup is enabled and if accounts will be enabled by the user via email
		if($config['users']['signup'] && $config['users']['activation'] === 1)
			$menu[] = $this->Html->link(__d('me_cms', 'Resend activation email'), array('controller' => 'profiles', 'action' => 'resend_activation'));
		
		//If reset password is enabled
		if($config['users']['reset_password'])
			$menu[] = $this->Html->link(__d('me_cms', 'Forgot your password?'), array('controller' => 'profiles', 'action' => 'forgot_password'));
		
		if(!empty($menu))
			echo $this->Html->ul($menu, array('class' => 'list-unstyled'));
	?>
</div>