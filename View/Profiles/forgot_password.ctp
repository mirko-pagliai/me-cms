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
 * @package		MeCms\View\Profiles
 */
?>

<div class="profiles form">
	<?php echo $this->Html->h2(__d('me_cms', 'Forgot your password?')); ?>
	<?php echo $this->Form->create('User'); ?>
		<fieldset>
			<?php
				echo $this->Form->input('email', array(
					'autofocus'	=> TRUE,
					'label'		=> __d('me_cms', 'Email')
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Request new password'), array('class' => 'btn-block btn-lg btn-primary')); ?>
	<?php
		echo $this->Html->ul(array(
			$this->Html->link(__d('me_cms', 'Login'), '/login'),
			$this->Html->link(__d('me_cms', 'Sign up'), array('controller' => 'profiles', 'action' => 'signup'))
		), array('class' => 'list-unstyled'));
	?>
</div>