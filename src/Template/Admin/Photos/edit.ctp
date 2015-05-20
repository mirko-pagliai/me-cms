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

<?php $this->assign('title', __d('me_cms', 'Edit photo')); ?>

<div class="photos form">
	<?= $this->Html->h2(__d('me_cms', 'Edit photo')) ?>
    <?= $this->Form->create($photo); ?>
	<div class='float-form'>
		<?php
			echo $this->Form->input('album_id', [
				'label' => __d('me_cms', 'Album')
			]);
		?>
	</div>
    <fieldset>
        <?php
			echo $this->Html->para(NULL, $this->Html->strong(__d('me_cms', 'Preview')));
			echo $this->Thumb->img($photo->path, ['class' => 'img-thumbnail margin-15']);
			
			echo $this->Form->input('filename', [
				'disabled'	=> TRUE,
				'label'		=> __d('me_cms', 'Filename')
			]);
			echo $this->Form->input('description', [
				'label'	=> __d('me_cms', 'Description'),
				'rows'	=> 3,
				'type'	=> 'textarea'
			]);
        ?>
    </fieldset>
    <?= $this->Form->submit(__d('me_cms', 'Edit photo')) ?>
    <?= $this->Form->end() ?>
</div>