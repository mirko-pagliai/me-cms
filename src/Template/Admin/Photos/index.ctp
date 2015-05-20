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

<?php $this->assign('title', __d('me_cms', 'Photos')); ?>

<div class="photos index">
	<?= $this->Html->h2(__d('me_cms', 'Photos')) ?>
	<div class='clearfix'>
		<?php foreach($photos as $photo): ?>
			<div class="col-sm-6 col-md-4 col-lg-3">
				<div class="photo-box">
					<?php
						echo $this->Html->div('photo-title', $photo->filename);
						echo $this->Html->div('photo-image', $this->Thumb->img($photo->path, ['side' => 400]));

						$actions = [
							$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $photo->id], ['icon' => 'pencil'])
						];

						//Only admins and managers can delete photos
						if($this->Auth->isGroup(['admin', 'manager']))
							$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $photo->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);

						//TO-DO: "_name"
						$actions[] = $this->Html->link(__d('me_cms', 'Open'), ['action' => 'view', $photo->id, 'prefix' => FALSE], ['icon' => 'external-link', 'target' => '_blank']);

						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?= $this->element('MeTools.paginator') ?>
</div>