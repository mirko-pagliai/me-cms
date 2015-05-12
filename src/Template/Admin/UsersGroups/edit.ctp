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
	
<?php $this->assign('title', __('Edit users group')); ?>

<div class="usersGroups form">
	<?= $this->Html->h2(__('Edit Users Group')) ?>
    <?= $this->Form->create($group); ?>
    <fieldset>
        <?php
			echo $this->Form->input('name', [
				'disabled'	=> TRUE,
				'label'		=> __d('me_cms', 'Name')
			]);
			echo $this->Form->input('label', [
				'label' => __d('me_cms', 'Label')
			]);
			echo $this->Form->input('description', [
				'label'	=> __d('me_cms', 'Description'),
				'rows'	=> 3,
				'type'	=> 'textarea'
			]);
        ?>
    </fieldset>
    <?= $this->Form->submit(__('Edit Users Group')) ?>
    <?= $this->Form->end() ?>
</div>