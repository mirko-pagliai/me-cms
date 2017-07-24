<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
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
                    <?= $this->Html->link(
                        $banner->position->title,
                        ['?' => ['position' => $banner->position->id]],
                        ['title' => __d('me_cms', 'View items that belong to this category')]
                    ) ?>
                </div>
                <div class="photo-created">
                    (<?= $banner->created->i18nFormat(getConfigOrFail('main.datetime.long')) ?>)
                </div>
                <div class="photo-image">
                    <?php
                    if ($banner->thumbnail) {
                        echo $this->Thumb->resize($banner->path, ['width' => 400]);
                    } else {
                        echo $this->Html->img($banner->www);
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
                    $actions[] = $this->Html->link(null, $banner->target, [
                        'icon' => 'external-link',
                        'title' => __d('me_cms', 'Open'),
                        'target' => '_blank'
                    ]);
                }

                $actions[] = $this->Html->link(
                    null,
                    ['action' => 'download', $banner->id],
                    ['icon' => 'download', 'title' => __d('me_cms', 'Download')]
                );

                //Only admins can delete banners
                if ($this->Auth->isGroup('admin')) {
                    $actions[] = $this->Form->postLink(null, ['action' => 'delete', $banner->id], [
                        'class' => 'text-danger',
                        'icon' => 'trash-o',
                        'title' => __d('me_cms', 'Delete'),
                        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                    ]);
                }

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>