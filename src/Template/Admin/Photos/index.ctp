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

<?= $this->extend('/Common/Admin/Photos/index') ?>
    
<table class="table table-hover">
    <thead>
        <tr>
            <th><?= $this->Paginator->sort('filename', __d('me_cms', 'Filename')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Albums.title', __d('me_cms', 'Album')) ?></th>
            <th class="text-center"><?= __d('me_cms', 'Description') ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Photos.created', __d('me_cms', 'Date')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($photos as $photo): ?>
            <tr>
                <td>
                    <strong><?= $this->Html->link($photo->filename, ['action' => 'edit', $photo->id]) ?></strong>
                    <?php                            
                        $actions = [
                            $this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $photo->id], ['icon' => 'pencil']),
                            $this->Html->link(__d('me_cms', 'Download'), ['action' => 'download', $photo->id], ['icon' => 'download']),
                        ];

                        //Only admins can delete photos
                        if($this->Auth->isGroup('admin')) {
                            $actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), ['action' => 'delete', $photo->id], ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?')]);
                        }

                        echo $this->Html->ul($actions, ['class' => 'actions']);								
                    ?>
                </td>
                <td class="text-center">
                    <?= $this->Html->link($photo->album->title, ['?' => ['position' => $photo->album->id]], ['title' => __d('me_cms', 'View items that belong to this category')]) ?>
                </td>
                <td class="text-center">
                    <?= $photo->description ?>
                </td>
                <td class="min-width text-center">
                    <div class="hidden-xs"><?= $photo->created->i18nFormat(config('main.datetime.long')) ?></div>
                    <div class="visible-xs">
                        <div><?= $photo->created->i18nFormat(config('main.date.short')) ?></div>
                        <div><?= $photo->created->i18nFormat(config('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>