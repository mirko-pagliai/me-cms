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
 * @package		MeCms\View\Banners
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('banners', 'nav')); ?>

<div class="banners form">
	<?php echo $this->Html->h2(__d('me_cms', 'Add banner')); ?>
	
	<div class="well">
		<?php 
			echo $this->Form->createInline(FALSE, array('type' => 'get'));
			echo $this->Form->label('filename', __d('me_cms', 'Banner'));
			echo $this->Form->input('filename', array(
				'default'	=> empty($this->request->query['file']) ? NULL : $this->request->query['file'],
				'label'		=> __d('me_cms', 'Banner'),
				'name'		=> 'file',
				'onchange'	=> 'send_form(this)',
				'options'	=> $tmpFiles,
				'type'		=> 'select'
			));
			echo $this->Form->end(__d('me_cms', 'Select'), array('div' => false));
		?>
	</div>
	
	<?php if(!empty($tmpFile)): #If the file to be used has been specified ?>
		<?php echo $this->Form->create('Banner'); ?>
			<div class='float-form'>
				<?php
					echo $this->Form->input('position_id', array(
						'label' => __d('me_cms', 'Position')
					));
					echo $this->Form->input('active', array(
						'checked'	=> TRUE,
						'label'		=> sprintf('%s?', __d('me_cms', 'Published'))
					));
				?>
			</div>
			<fieldset>
				<?php
					echo $this->Html->thumb($tmpFile['path'], array('class' => 'img-thumbnail margin-15'));
					echo $this->Form->input('filename', array(
						'label'		=> __d('me_cms', 'Filename'),
						'readonly'	=> TRUE,
						'value'		=> $tmpFile['filename']
					));
					echo $this->Form->input('target', array(
						'label' => __d('me_cms', 'Web address'),
						'tip'	=> __d('me_cms', 'The address should begin with %s', $this->Html->em('http://'))
					));
					echo $this->Form->input('description', array(
						'label'	=> __d('me_cms', 'Description'),
						'rows'	=> 2,
						'type'	=> 'textarea'
					));
				?>
			</fieldset>
		<?php echo $this->Form->end(__d('me_cms', 'Add banner')); ?>
	<?php endif; ?>
</div>