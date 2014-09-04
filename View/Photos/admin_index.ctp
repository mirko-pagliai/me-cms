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
 * @package		MeCmsBackend\View\Photos
 */
?>
	
<?php
	$this->extend('/Common/photos');
	$this->Html->css('/MeCmsBackend/css/photos');
?>
	
<div class="photos index">
	<?php echo $this->Html->h2(__d('me_cms_backend', 'Photos')); ?>
	<div class='clearfix'>
		<?php foreach($photos as $k => $photo): ?>
			<?php if($k%4 === 0) echo '<div class=\'row\'>'; ?>
			<div class='col-md-3'>
				<div class='photo-box'>
					<?php echo $this->Html->para('photo-filename text-center', $this->Html->strong($photo['Photo']['filename'])); ?>
					<div class='relative'>
						<?php
							echo $this->Html->thumb($path.DS.$photo['Photo']['filename'], array('side' => '270'));

							if(!empty($photo['Photo']['description']))
								echo $this->Html->div('photo-description', $photo['Photo']['description']);
						?>
					</div>
					<div class='photo-links'>
						<?php
							echo $this->Html->link(__d('me_cms_backend', 'Edit'), array('action' => 'edit', $photo['Photo']['id']), array('icon' => 'pencil'));
							echo $this->Form->postLink(__d('me_cms_backend', 'Delete'), array('action' => 'delete', $photo['Photo']['id']), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms_backend', 'Are you sure you want to delete this photo?'));
						?>
					</div>
				</div>
			</div>
			<?php if($k%4 === 3 || $k +1 === count($photos)) echo '</div>'; ?>
		<?php endforeach; ?>
	</div>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>