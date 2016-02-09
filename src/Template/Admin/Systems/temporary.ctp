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
	
<?php $this->assign('title', __d('me_cms', 'Temporary files')); ?>

<div class="systems index">
	<?php if($this->Auth->isGroup('admin')): //Only admins can clear all temporary files ?>
		<?= $this->Html->h2(__d('me_cms', 'All temporary files')) ?>
		<div class="margin-20">
			<?php
				echo $this->Html->h4(__d('me_cms', 'Cache'));

				echo $this->Html->para(NULL, __d('me_cms', 'All temporary files size: {0}', $this->Number->toReadableSize($all_size)));
				echo $this->Html->para(NULL, __d('me_cms', 'This command clear all temporary files: cache, assets, logs and thumbnails'));
				echo $this->Form->postButton(__d('me_cms', 'Clear all temporary files'), ['action' => 'clear_all'], ['class' => 'btn-success', 'icon' => 'trash-o']);
			?>
		</div>

		<hr />
	<?php endif; ?>
	
	<div class="margin-20">
		<?php
			echo $this->Html->h4(__d('me_cms', 'Cache'));
			
			if(!$cache_status)
				echo $this->Html->para('text-danger', __d('me_cms', 'The cache is disabled or debugging is active'));

			echo $this->Html->para(NULL, __d('me_cms', 'Cache size: {0}', $this->Number->toReadableSize($cache_size)));
			echo $this->Html->para(NULL, __d('me_cms', 'Note: you should not need to clear the cache, unless you have not edited the configuration or after an upgrade'));
			echo $this->Form->postButton(__d('me_cms', 'Clear cache'), ['action' => 'clear_cache'], ['class' => 'btn-success', 'icon' => 'trash-o']);
		?>
	</div>
	
	<div class="margin-20">
		<?php
			echo $this->Html->h4(__d('me_cms', 'Assets'));
			echo $this->Html->para(NULL, __d('me_cms', 'Assets size: {0}', $this->Number->toReadableSize($assets_size)));

			if($assets_size)
				echo $this->Form->postButton(__d('me_cms', 'Clear assets'), ['action' => 'clear_assets'], ['class' => 'btn-success', 'icon' => 'trash-o']);
		?>
	</div>
	
	<?php if($this->Auth->isGroup('admin')): //Only admins can clear logs ?>
		<div class="margin-20">
			<?php
				echo $this->Html->h4(__d('me_cms', 'Logs'));
				echo $this->Html->para(NULL, __d('me_cms', 'Logs size: {0}', $this->Number->toReadableSize($logs_size)));

				if($logs_size)
					echo $this->Form->postButton(__d('me_cms', 'Clear logs'), ['action' => 'clear_logs'], ['class' => 'btn-success', 'icon' => 'trash-o']);
			?>
		</div>
	<?php endif; ?>
	
	<div class="margin-20">
		<?php
			echo $this->Html->h4(__d('me_cms', 'Thumbnails'));
			echo $this->Html->para(NULL, __d('me_cms', 'Thumbnails size: {0}', $this->Number->toReadableSize($thumbs_size)));

			if($thumbs_size) {
				echo $this->Html->para(NULL, __d('me_cms', 'Note: you should not need to clear the thumbnails and that this will slow down the images loading the first time that are displayed. You should clear thumbnails only when they have reached a large size or when many images are no longer used'));
				echo $this->Form->postButton(__d('me_cms', 'Clear Thumbnails'), ['action' => 'clear_thumbs'], ['class' => 'btn-success', 'icon' => 'trash-o']);
			}
		?>
	</div>
</div>