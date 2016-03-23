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
	
<?php $this->assign('title', __d('me_cms', 'Banners')); ?>

<div class="banners index">
	<?= $this->Html->h2(__d('me_cms', 'Banners')) ?>
	<?= $this->Html->button(__d('me_cms', 'Upload'), ['action' => 'upload'], ['class' => 'btn-success', 'icon' => 'plus']) ?>
	
	<?php echo $this->Form->createInline(NULL, ['class' => 'filter-form', 'type' => 'get']); ?>
		<fieldset>
			<legend><?= __d('me_cms', 'Filter').$this->Html->icon('eye') ?></legend>
			<div>
				<?php
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
			</div>
		</fieldset>
	<?php echo $this->Form->end(); ?>
	
    <table class="table table-hover">
		<thead>
			<tr>
				<th><?php echo $this->Paginator->sort('filename', __d('me_cms', 'Filename')); ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('Positions.name', __d('me_cms', 'Position')); ?></th>
				<th class="text-center hidden-xs"><?= __d('me_cms', 'Url') ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('description', __d('me_cms', 'Description')); ?></th>
				<th class="text-center"><?php echo $this->Paginator->sort('click_count', __d('me_cms', 'Click')); ?></th>
				<th class="text-center"><?= $this->Paginator->sort('created', __d('me_cms', 'Date')) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($banners as $banner): ?>
				<tr>
					<td>
                        <strong><?= $this->Html->link($banner->filename, ['action' => 'edit', $banner->id]) ?></strong>
						<?php
                            //If the banner is not active (not published)
                            if(!$banner->active)
                                echo $this->Html->span(__d('me_cms', 'Not published'), ['class' => 'record-label']);
			
							$actions = [
								$this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $banner->id], ['icon' => 'pencil'])
							];
							
							if(!empty($banner->target))
								$actions[] = $this->Html->link(__d('me_cms', 'Open'), $banner->target, ['icon' => 'external-link', 'target' => '_blank']);
							
							//Only admins can delete banners
							if($this->Auth->isGroup('admin'))
								$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $banner->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
															
							echo $this->Html->ul($actions, ['class' => 'actions']);								
						?>
					</td>
					<td class="text-center">
						<?= $this->Html->link($banner->position->name, ['?' => ['position' => $banner->position->id]], ['title' => __d('me_cms', 'View items that belong to this category')]) ?>
					</td>
					<td class="text-center hidden-xs">
                        <?php
                            if($banner->target) {
                                $truncated = $this->Text->truncate($banner->target, 50, ['exact' => FALSE]);
                                echo $this->Html->link($truncated, $banner->target, ['target' => '_blank']);
                            }
                        ?>
					</td>
					<td class="text-center"><?= $banner->description ?></td>
					<td class="min-width text-center"><?= $banner->click_count ?></td>
					<td class="min-width text-center">
						<div class="hidden-xs"><?= $banner->created->i18nFormat(config('main.datetime.long')) ?></div>
						<div class="visible-xs">
							<div><?= $banner->created->i18nFormat(config('main.date.short')) ?></div>
							<div><?= $banner->created->i18nFormat(config('main.time.short')) ?></div>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
    </table>
	<?= $this->element('MeTools.paginator') ?>
</div>