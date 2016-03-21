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

<?php $this->assign('title', __d('me_cms', 'Database backups')); ?>

<div class="backups index">
	<?= $this->Html->h2(__d('me_cms', 'Database backups')) ?>
	<?= $this->Html->button(__d('me_cms', 'Add'), ['action' => 'add'], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
	<table class="table table-striped">
		<tr>
			<th><?= __d('me_cms', 'Filename') ?></th>
			<th class="min-width text-center"><?= __d('me_cms', 'Compression') ?></th>
			<th class="min-width text-center"><?= __d('me_cms', 'Date') ?></th>
		</tr>
		<?php foreach($backups as $backup): ?>
			<tr>
				<td>
					<?php 
						$title = $this->Html->link($backup->filename, ['action' => 'download', urlencode($backup->filename)]);
						
						echo $this->Html->strong($title);
						
						$actions = [
							$this->Html->link(__d('me_cms', 'Download'), ['action' => 'download', urlencode($backup->filename)], ['icon' => 'download']),
							$this->Form->postLink(__d('me_cms', 'Restore'), ['action' => 'restore', urlencode($backup->filename)], ['icon' => 'upload', 'confirm' => __d('me_cms', 'This will overwrite the current database and some data may be lost. Are you sure?')]),
							$this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', urlencode($backup->filename)], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]),
						];
						
						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</td>
				<td class="min-width text-center"><?= $backup->compression ?></td>
				<td class="min-width text-center"><?= $backup->datetime->i18nFormat(config('main.datetime.long')) ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>