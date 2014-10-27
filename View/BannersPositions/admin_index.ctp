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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\BannersPositions
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('banners', 'nav')); ?>
	
<div class="bannersPositions index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'Banners positions'));
		echo $this->Html->button(__d('me_cms', 'Add new'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('name', __d('me_cms', 'Name')); ?></th>
			<th><?php echo $this->Paginator->sort('description', __d('me_cms', 'Description')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('banner_count', __d('me_cms', 'Banners')); ?></th>
		</tr>
		<?php foreach($positions as $position): ?>
			<tr>
				<td>
					<?php 
						$title = $this->Html->link($position['BannersPosition']['name'], array('action' => 'edit', $id = $position['BannersPosition']['id']));
						echo $this->Html->strong($title);
												
						echo $this->Html->ul(array(
							$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $id), array('icon' => 'pencil')),
							$this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $id), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this?'))
						), array('class' => 'actions'));
					?>
				</td>
				<td><?php echo $position['BannersPosition']['description']; ?></td>
				<td class="min-width text-center">
					<?php echo $position['BannersPosition']['banner_count']; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>