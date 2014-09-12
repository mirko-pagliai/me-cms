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
 * @package		MeCms\View\Photos
 */
?>
	
<?php
	$this->assign('sidebar', $this->Menu->get('photos', 'nav'));
	$this->Html->css('/MeCms/css/backend/photos');
	$this->Html->js('/MeCms/js/photos');
?>

<div class="photos form">
	<?php echo $this->Html->h2(__d('me_cms', 'Add photos')); ?>
	<?php echo $this->Form->create('Photo'); ?>
		<div class='float-form'>
			<?php echo $this->Form->input('album_id', array('default' => $albumId)); ?>
		</div>
		<fieldset>
			<div class='clearfix'>
				<div class='btn-group margin-10'>
					<?php
						echo $this->Html->button(__d('me_cms', 'Check all'), '#', array('class' => 'check-all btn-primary', 'icon' => 'check-square-o'));
						echo $this->Html->button(__d('me_cms', 'Uncheck all'), '#', array('class' => 'uncheck-all btn-primary', 'icon' => 'minus-square-o'));
					?>
				</div>
				<?php foreach($photos as $k => $photo): ?>
					<?php if($k%4 === 0) echo '<div class=\'row\'>'; ?>
					<div class='col-md-3'>
						<div class='photo-box'>
							<?php
								echo $this->Form->input(sprintf('Photo.%s.filename', $k), array(
									'div'			=> array('class' => 'photo-filename'),
									'hiddenField'	=> FALSE,
									'label'			=> $photo,
									'type'			=> 'checkbox',
									'value'			=> $photo
								));
								echo $this->Html->thumb($tmpPath.DS.$photo, array('side' => '184'));
								echo $this->Form->input(sprintf('Photo.%s.description', $k), array(
									'div'			=> array('class' => 'photo-description'),
									'label'			=> FALSE,
									'placeholder'	=> __d('me_cms', 'Description...'),
									'rows'			=> 2,
									'type'			=> 'textarea'
								));
							?>
						</div>
					</div>
					<?php if($k%4 === 3 || $k +1 === count($photos)) echo '</div>'; ?>
				<?php endforeach; ?>
			</div>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Add photos')); ?>
</div>