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
 * @package		MeCms\View\Photos
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('photos', 'nav')); ?>

<div class="photos form">
	<?php echo $this->Html->h2(__d('me_cms', 'Edit photo')); ?>
	<?php echo $this->Form->create('Photo'); ?>
		<div class="float-form">
			<?php echo $this->Form->input('album_id', array('label' => __d('me_cms', 'Album'))); ?>
		</div>
		<fieldset>
			<div class="clearfix">
				<div class="photo-box">
					<?php
						echo $this->Html->div('title', $photo['Photo']['filename']);
						echo $this->Html->thumb($photo['Photo']['path'], array('side' => 400));
					?>
				</div>
			</div>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('description', array(
					'label' => __d('me_cms', 'Description'),
					'rows'	=> 2,
					'type'	=> 'textarea'
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Edit photo')); ?>
</div>