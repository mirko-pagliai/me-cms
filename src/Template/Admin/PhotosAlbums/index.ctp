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

<?php $this->assign('title', __d('me_cms', 'Photos albums')); ?>

<div class="photosAlbums index">
	<?= $this->Html->h2(__d('me_cms', 'Photos albums')) ?>
	<?= $this->Html->button(__d('me_cms', 'Add'), ['action' => 'add'], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
    <table class="table table-hover">
		<thead>
			<tr>
				<th><?= $this->Paginator->sort('title', __d('me_cms', 'Title')) ?></th>
				<th class="text-center"><?= $this->Paginator->sort('description', __d('me_cms', 'Description')) ?></th>
				<th class="min-width text-center"><?= $this->Paginator->sort('photo_count', __d('me_cms', 'Photos')) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($albums as $album): ?>
				<tr>
					<td>
					
						<?php
							$title = $this->Html->link($album->title, ['controller' => 'Photos', $album->id]);

							//If the album is not active (not published)
							if(!$album->active)
								$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Not published'), ['class' => 'text-warning']));

							echo $this->Html->strong($title);

							$actions = [
								$this->Html->link(__d('me_cms', 'View'), ['controller' => 'Photos', $album->id], ['icon' => 'eye']),
								$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $album->id], ['icon' => 'pencil'])
							];

							//Only admins  can delete albums
							if($this->Auth->isGroup('admin'))
								$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $album->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);

							$actions[] = $this->Html->link(__d('me_cms', 'Open'), ['_name' => 'album', $album->slug], ['icon' => 'external-link', 'target' => '_blank']);

							echo $this->Html->ul($actions, ['class' => 'actions']);
						?>
					</td>
					<td class="text-center"><?= $album->description ?></td>
					<td class="min-width text-center"><?= $album->photo_count ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
	<?= $this->element('MeTools.paginator') ?>
</div>