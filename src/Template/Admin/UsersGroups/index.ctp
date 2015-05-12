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
	
<?php $this->assign('title', __d('me_cms', 'List users groups')); ?>

<div class="usersGroups index">
	<?= $this->Html->h2(__d('me_cms', 'List users groups')) ?>
	<?= $this->Html->button(__d('me_cms', 'Add'), ['action' => 'add'], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
    <table class="table table-hover">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('name', __d('me_cms', 'Name')) ?></th>
				<th><?= $this->Paginator->sort('label', __d('me_cms', 'Label')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('user_count', __d('me_cms', 'Users')) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($groups as $group): ?>
				<tr>
					<td>
						<?php
							$title = $this->Html->link($group->name, ['action' => 'edit', $group->id]);
							echo $this->Html->strong($title);

							echo $this->Html->ul([
								$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $group->id], ['icon' => 'pencil']),
								$this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $group->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')])
							], ['class' => 'actions']);
						?>
					</td>
					<td><?= $group->description ?></td>
					<td class="min-width text-center"><?= $group->user_count ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
	<?= $this->element('MeTools.paginator') ?>
</div>