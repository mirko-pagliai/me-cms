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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php $this->assign('title', $album->title); ?>

<div class="photosAlbums index">
	<?= $this->Html->h2($album->title) ?>
	<?= $this->Html->button(__d('me_cms', 'Upload'), ['controller' => 'Photos', 'action' => 'upload', '?' => ['album' => $album->id]], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
	<div class='clearfix'>
		<?php foreach($album->photos as $photo): ?>
			<div class="col-sm-6 col-md-4 col-lg-3">
				<div class="photo-box">
					<div class="photo-title">
                        <?= $photo->filename ?>
                    </div>
					<div class="photo-created">
                        (<?= $photo->created->i18nFormat(config('main.datetime.long')) ?>)
                    </div>
					<div class="photo-image">
                        <?= $this->Thumb->image($photo->path, ['side' => 400]) ?>
                    </div>
					
					<?php
						$actions = [
							$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $photo->id], ['icon' => 'pencil'])
						];

						//Only admins and managers can delete photos
						if($this->Auth->isGroup(['admin', 'manager']))
							$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $photo->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);

						$actions[] = $this->Html->link(__d('me_cms', 'Open'), ['_name' => 'photo', $album->slug, $photo->id], ['icon' => 'external-link', 'target' => '_blank']);

						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?= $this->element('MeTools.paginator') ?>
</div>