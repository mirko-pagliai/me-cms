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
 * @package		MeCmsBackend\View\Posts
 */
?>
	
<?php 
	$this->extend('/Common/posts');
	$this->Library->slugify();
	$this->Library->ckeditor();
	$this->Library->datetimepicker();
?>

<div class="posts form">
	<?php echo $this->Html->h2(__d('me_cms_backend', 'Add post')); ?>
	<?php echo $this->Form->create('Post', array('class' => 'form-base')); ?>
		<div class='float-form'>
			<?php
				echo $this->Form->input('user_id', array(
					'default'	=> $auth['id'],
					'label'		=> __d('me_cms_backend', 'Author')
				));
				echo $this->Form->input('category_id');
				echo $this->Form->datetimepicker('created', array(
					'tip' => array(
						sprintf('%s.', __d('me_cms_backend', 'If blank, the current date and time will be used')),
						sprintf('%s.', __d('me_cms_backend', 'You can delay the publication by entering a future date'))
					)
				));
				echo $this->Form->input('priority', array(
					'default'	=> '3',
					'options'	=> array(
						'1' => sprintf('1 - %s', __d('me_cms_backend', 'Very low')),
						'2' => sprintf('2 - %s', __d('me_cms_backend', 'Low')),
						'3' => sprintf('3 - %s', __d('me_cms_backend', 'Normal')),
						'4' => sprintf('4 - %s', __d('me_cms_backend', 'High')),
						'5' => sprintf('5 - %s', __d('me_cms_backend', 'Very high')),
					)
				));
				echo $this->Form->input('active', array(
					'checked'	=> TRUE,
					'label'		=> sprintf('%s?', __d('me_cms_backend', 'Published')),
					'tip'		=> __d('me_cms_backend', 'Disable this option to save as a draft')
				));
			?>
		</div>
		<fieldset>
			<?php
				echo $this->Form->input('title', array('id' => 'title'));
				echo $this->Form->input('slug', array(
					'id'	=> 'slug',
					'tip'	=> __d('me_cms_backend', 'The slug is a string identifying a resource. If you do not have special needs, let it be generated automatically')
				));
				echo $this->Form->ckeditor('text');
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms_backend', 'Add post')); ?>
</div>