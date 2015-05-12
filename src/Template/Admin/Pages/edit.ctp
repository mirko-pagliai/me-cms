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
 */
?>

<?php
	$this->assign('title', __d('me_cms', 'Edit page'));
	$this->Library->ckeditor();
	$this->Library->datetimepicker();
	$this->Library->slugify();
?>

<div class="pages form">
	<?= $this->Html->h2(__d('me_cms', 'Edit Page')) ?>
    <?= $this->Form->create($page); ?>
	<div class='float-form'>
		<?php
            echo $this->Form->datetimepicker('created', [
				'label'	=> __d('me_cms', 'Date'),
				'tip'	=> [
					__d('me_cms', 'If blank, the current date and time will be used'),
					__d('me_cms', 'You can delay the publication by entering a future date')
				],
				'value'	=> $page->created->i18nFormat(FORMAT_FOR_MYSQL)
			]);
            echo $this->Form->input('priority', [
				'label' => __d('me_cms', 'Priority')
			]);
            echo $this->Form->input('active', [
				'label'	=> sprintf('%s?', __d('me_cms', 'Published')),
				'tip'	=> __d('me_cms', 'Disable this option to save as a draft')
			]);
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
    <?= $this->Form->submit(__d('me_cms', 'Edit page')) ?>
    <?= $this->Form->end() ?>
</div>