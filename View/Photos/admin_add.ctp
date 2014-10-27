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
	
<?php $this->assign('sidebar', $this->Menu->get('photos', 'nav')); ?>

<div class="photos form">
	<?php echo $this->Html->h2(__d('me_cms', 'Add photos')); ?>
	<?php echo $this->Form->create('Photo'); ?>
		<div class="float-form">
			<?php
				$options = array();
				//If it's already specified a album ID
				if(!empty($albumId))
					$options['default'] = $albumId;
				//Else, if there's only one album
				elseif(count($albums) < 2)
					$options['default'] = $albums[1];
				
				echo $this->Form->input('album_id', am($options, array('label' => __d('me_cms', 'Album'))));
			?>
		</div>
		<fieldset>
			<div class="btn-group margin-10 clearfix">
				<?php
					echo $this->Html->button(__d('me_cms', 'Check all'), '#', array('class' => 'check-all btn-primary', 'icon' => 'check-square-o'));
					echo $this->Html->button(__d('me_cms', 'Uncheck all'), '#', array('class' => 'uncheck-all btn-primary', 'icon' => 'minus-square-o'));
				?>
			</div>
			<div class="clearfix">
				<?php foreach($photos as $k => $photo): ?>
					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="photo-box">
							<?php
								echo $this->Form->input(sprintf('Photo.%s.filename', $k), array(
									'checked'		=> TRUE,
									'div'			=> array('class' => 'title'),
									'hiddenField'	=> FALSE,
									'label'			=> $photo,
									'type'			=> 'checkbox',
									'value'			=> $photo
								));
								echo $this->Html->thumb($tmpPath.DS.$photo, array('side' => 263));
								echo $this->Form->input(sprintf('Photo.%s.description', $k), array(
									'div'			=> array('class' => 'description'),
									'label'			=> FALSE,
									'placeholder'	=> sprintf('%s...', __d('me_cms', 'Description')),
									'rows'			=> 2,
									'type'			=> 'textarea'
								));
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Add photos')); ?>
</div>