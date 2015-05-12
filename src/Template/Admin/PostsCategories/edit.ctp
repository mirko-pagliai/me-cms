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
	$this->assign('title', __d('me_cms', 'Edit posts category'));
	$this->Library->slugify();
?>

<div class="postsCategories form">
	<?= $this->Html->h2(__d('me_cms', 'Edit Posts Category')) ?>
    <?= $this->Form->create($category); ?>
	<div class='float-form'>
		<?php
			if(!empty($categories))
				echo $this->Form->input('parent_id', [
					'empty'		=> FALSE,
					'label'		=> __d('me_cms', 'Parent category'),
					'options'	=> $categories,
					'tip'		=> __d('me_cms', 'Leave blank to create a parent category')
				]);
		?>
	</div>
    <fieldset>
        <?php
			echo $this->Form->input('title', [
				'id'	=> 'title',
				'label'	=> __d('me_cms', 'Title')
			]);
			echo $this->Form->input('slug', [
				'id'	=> 'slug',
				'label'	=> __d('me_cms', 'Slug'),
				'tip'	=> __d('me_cms', 'The slug is a string identifying a resource. If you do not have special needs, let it be generated automatically')
			]);
            echo $this->Form->input('description', [
				'label'	=> __d('me_cms', 'Description'),
				'rows'	=> 3
			]);
        ?>
    </fieldset>
    <?= $this->Form->submit(__d('me_cms', 'Edit Posts Category')) ?>
    <?= $this->Form->end() ?>
</div>