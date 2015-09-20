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
	
<?php $this->assign('title', __d('me_cms', 'Banners')); ?>

<div class="banners index">
	<?= $this->Html->h2(__d('me_cms', 'Banners')) ?>
	<?= $this->Html->button(__d('me_cms', 'Add'), ['action' => 'add'], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
	<?php echo $this->Form->createInline(NULL, ['class' => 'filter-form', 'type' => 'get']); ?>
		<fieldset>
			<?php
				echo $this->Form->legend(__d('me_cms', 'Filter'));
				echo $this->Form->input('filename', [
					'default'		=> $this->request->query('filename'),
					'placeholder'	=> __d('me_cms', 'filename'),
					'size'			=> 16
				]);
				echo $this->Form->input('active', [
					'default'	=> $this->request->query('active'),
					'empty'		=> sprintf('-- %s --', __d('me_cms', 'all status')),
					'options'	=> ['yes' => __d('me_cms', 'Only published'), 'no' => __d('me_cms', 'Only not published')]
				]);
				echo $this->Form->input('position', [
					'default'	=> $this->request->query('position'),
					'empty'		=> sprintf('-- %s --', __d('me_cms', 'all positions'))
				]);
				echo $this->Form->submit(NULL, ['icon' => 'search']);
			?>
		</fieldset>
	<?php echo $this->Form->end(); ?>
	
    <table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo $this->Paginator->sort('filename', __d('me_cms', 'Filename')); ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('position_id', __d('me_cms', 'Position')); ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('target', __d('me_cms', 'Url')); ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('description', __d('me_cms', 'Description')); ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('click_count', __d('me_cms', 'Click')); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($banners as $banner): ?>
				<tr>
					<td>
						<?php
							$title = $this->Html->link($banner->filename, ['action' => 'edit', $banner->id]);

							//If the banner is not active (not published)
							if(!$banner->active)
								$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Not published'), ['class' => 'text-warning']));

							echo $this->Html->strong($title);
			
							$actions = [
								$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $banner->id], ['icon' => 'pencil'])
							];
							
							//Only admins can delete banners
							if($this->Auth->isGroup('admin'))
								$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $banner->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
															
							echo $this->Html->ul($actions, ['class' => 'actions']);								
						?>
					</td>
					<td class="text-center"><?= $banner->position->name ?></td>
					<td class="text-center">
						<?= empty($banner->target) ? NULL : $this->Html->link($banner->target, $banner->target, ['target' => '_blank']) ?>
					</td>
					<td class="text-center"><?= $banner->description ?></td>
					<td class="min-width text-center"><?= $banner->click_count ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
	<?= $this->element('MeTools.paginator') ?>
</div>