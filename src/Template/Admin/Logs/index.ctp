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

<?php $this->assign('title', __d('me_cms', 'Logs')); ?>

<div class="logs index">
	<?= $this->Html->h2(__d('me_cms', 'Logs')) ?>
	<table class="table table-striped">
		<tr>
			<th><?= __d('me_cms', 'Filename') ?></th>
			<th class="text-center"><?= __d('me_cms', 'Type') ?></th>
			<th class="text-center"><?= __d('me_cms', 'Size') ?></th>
		</tr>
		<?php foreach($logs as $log): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($log->filename, ['action' => 'view', $log->slug]);
						
						echo $this->Html->strong($title);
						
						$actions = [
							$this->Html->link(__d('me_cms', 'Basic view'), ['action' => 'view', $log->slug], ['icon' => 'eye']),
						];
						
						if($log->serialized)
							$actions[] = $this->Html->link(__d('me_cms', 'Advanced view'), ['action' => 'view_serialized', $log->slug], ['icon' => 'eye']);
						
						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</td>
				<td class="min-width text-center">
					<?= $log->serialized ? __d('me_cms', 'Serialized') : __d('me_cms', 'Plain') ?>
				</td>
				<td class="min-width text-center">
					<?= $this->Number->toReadableSize($log->size) ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>