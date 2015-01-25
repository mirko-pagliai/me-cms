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
 * @package		MeCms\View\Pages
 */
?>
	
<?php
	$this->assign('sidebar', $this->Menu->get('pages', 'nav'));
	$this->Library->slugify();
	$this->Library->ckeditor();
	$this->Library->datetimepicker();
?>

<div class="pages form">
	<?php echo $this->Html->h2(__d('me_cms', 'Add page')); ?>
	<?php echo $this->Form->create('Page'); ?>
		<div class='float-form'>
			<?php
				echo $this->Form->datetimepicker('created', array(
					'label'	=> __d('me_cms', 'Date'),
					'tip'	=> array(
						sprintf('%s.', __d('me_cms', 'If blank, the current date and time will be used')),
						sprintf('%s.', __d('me_cms', 'You can delay the publication by entering a future date'))
					)
				));
				echo $this->Form->input('priority', array(
					'default'	=> '3',
					'label'		=> __d('me_cms', 'Priority'),
					'options'	=> array(
						'1' => sprintf('1 - %s', __d('me_cms', 'Very low')),
						'2' => sprintf('2 - %s', __d('me_cms', 'Low')),
						'3' => sprintf('3 - %s', __d('me_cms', 'Normal')),
						'4' => sprintf('4 - %s', __d('me_cms', 'High')),
						'5' => sprintf('5 - %s', __d('me_cms', 'Very high')),
					)
				));
				echo $this->Form->input('active', array(
					'checked'	=> TRUE,
					'label'		=> sprintf('%s?', __d('me_cms', 'Published')),
					'tip'		=> __d('me_cms', 'Disable this option to save as a draft')
				));
			?>
		</div>
		<fieldset>
			<?php
				echo $this->Form->input('title', array(
					'id'	=> 'title',
					'label'	=> __d('me_cms', 'Title')
				));
				echo $this->Form->input('subtitle', array(
					'label'	=> __d('me_cms', 'Subtitle')
				));
				echo $this->Form->input('slug', array(
					'id'	=> 'slug',
					'label'	=> __d('me_cms', 'Slug'),
					'tip'	=> __d('me_cms', 'The slug is a string identifying a resource. If you do not have special needs, let it be generated automatically')
				));
				echo $this->Form->ckeditor('text', array(
					'label' => __d('me_cms', 'Text')
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Add page')); ?>
</div>