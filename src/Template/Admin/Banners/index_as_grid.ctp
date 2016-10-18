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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */

$this->extend('/Admin/Common/Banners/index');
?>

<div class='clearfix'>
    <?php foreach ($banners as $banner) : ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="photo-box">
                <div class="photo-title">
                    <?= $this->Html->link($banner->filename, ['action' => 'edit', $banner->id]) ?>
                </div>
                <div class="photo-id">
                    <?= __d('me_cms', 'ID') ?> <code><?= $banner->id ?></code>
                </div>
                <div class="photo-album">
                    <?= __d('me_cms', 'Position') ?>: 
                    <?php
                        echo $this->Html->link(
                            $banner->position->title,
                            ['?' => ['position' => $banner->position->id]],
                            ['title' => __d('me_cms', 'View items that belong to this category')]
                        );
                    ?>
                </div>
                <div class="photo-created">
                    (<?= $banner->created->i18nFormat(config('main.datetime.long')) ?>)
                </div>
                <div class="photo-image">
                    <?php
                    if ($banner->thumbnail) {
                        echo $this->Thumb->resize($banner->path, ['width' => 400]);
                    } else {
                        echo $this->Html->img('banners/' . $banner->filename);
                    }
                    ?>
                </div>

                <?php
                $actions = [
                    $this->Html->link(
                        null,
                        ['action' => 'edit', $banner->id],
                        ['icon' => 'pencil', 'title' => __d('me_cms', 'Edit')]
                    ),
                ];

                if ($banner->target) {
                    $actions[] = $this->Html->link(
                        null,
                        $banner->target,
                        [
                            'icon' => 'external-link',
                            'title' => __d('me_cms', 'Open'),
                            'target' => '_blank'
                        ]
                    );
                }

                $actions[] = $this->Html->link(
                    null,
                    ['action' => 'download', $banner->id],
                    ['icon' => 'download', 'title' => __d('me_cms', 'Download')]
                );

                //Only admins can delete banners
                if ($this->Auth->isGroup('admin')) {
                    $actions[] = $this->Form->postLink(
                        null,
                        ['action' => 'delete', $banner->id],
                        [
                            'class' => 'text-danger',
                            'icon' => 'trash-o',
                            'title' => __d('me_cms', 'Delete'),
                            'confirm' => __d('me_cms', 'Are you sure you want to delete this?')
                        ]
                    );
                }

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>