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
	
    <?= $this->element('backend/list-grid-buttons') ?>
    
	<div class='clearfix'>
		<?php foreach($banners as $banner): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
				<div class="photo-box">
					<div class="photo-title">
                        <?= $banner->filename ?>
                    </div>
					<div class="photo-created">
                        (<?= $banner->created->i18nFormat(config('main.datetime.long')) ?>)
                    </div>
					<div class="photo-image">
                        <?= $this->Thumb->image($banner->path, ['side' => 400, 'force' => TRUE]) ?>
                    </div>
					
					<?php
                        $actions = [
                            $this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $banner->id], ['icon' => 'pencil'])
                        ];

                        if($banner->target) {
                            $actions[] = $this->Html->link(__d('me_cms', 'Open'), $banner->target, ['icon' => 'external-link', 'target' => '_blank']);
                        }

                        $actions[] = $this->Html->link(__d('me_cms', 'Download'), ['action' => 'download', $banner->id], ['icon' => 'download']);
                            
                        //Only admins can delete banners
                        if($this->Auth->isGroup('admin')) {
                            $actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $banner->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
                        }
                        
						echo $this->Html->ul($actions, ['class' => 'actions']);
					?>
				</div>
			</div>
        <?php endforeach; ?>
    </div>
    
	<?= $this->element('MeTools.paginator') ?>
</div>