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
$this->extend('/Admin/Common/Photos/index');
?>

<div class='clearfix'>
    <?php foreach ($photos as $photo) : ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="photo-box">
                <div class="photo-title">
                    <?= $this->Html->link($photo->filename, ['action' => 'edit', $photo->id]) ?>
                </div>
                <div class="photo-id">
                    <?= __d('me_cms', 'ID') ?> <code><?= $photo->id ?></code>
                </div>
                <div class="photo-album">
                    <?= __d('me_cms', 'Album') ?>:
                    <?= $this->Html->link(
                        $photo->album->title,
                        ['?' => ['album' => $photo->album->id]],
                        ['title' => __d('me_cms', 'View items that belong to this category')]
                    ) ?>
                </div>
                <div class="photo-created">
                    (<?= $photo->created->i18nFormat(getConfigOrFail('main.datetime.long')) ?>)
                </div>
                <div class="photo-image">
                    <?= $this->Thumb->fit($photo->path, ['width' => 400]); ?>
                </div>

                <?php
                $actions = [];
                $actions[] = $this->Html->link(
                    null,
                    ['action' => 'edit', $photo->id],
                    ['icon' => 'pencil', 'title' => __d('me_cms', 'Edit')]
                );
                $actions[] = $this->Html->link(
                    null,
                    ['action' => 'download', $photo->id],
                    ['icon' => 'download', 'title' => __d('me_cms', 'Download')]
                );

                //Only admins and managers can delete photos
                if ($this->Auth->isGroup(['admin', 'manager'])) {
                    $actions[] = $this->Form->postLink(
                        null,
                        ['action' => 'delete', $photo->id],
                        [
                            'class' => 'text-danger',
                            'icon' => 'trash-o',
                            'title' => __d('me_cms', 'Delete'),
                            'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                        ]
                    );
                }

                //If the photo is active
                if ($photo->active) {
                    $actions[] = $this->Html->link(
                        null,
                        ['_name' => 'photo', 'slug' => $photo->album->slug, 'id' => $photo->id],
                        [
                            'icon' => 'external-link',
                            'target' => '_blank',
                            'title' => __d('me_cms', 'Open'),
                        ]
                    );
                } else {
                    $actions[] = $this->Html->link(null, ['_name' => 'photosPreview', $photo->id], [
                        'icon' => 'external-link',
                        'target' => '_blank',
                        'title' => __d('me_cms', 'Preview'),
                    ]);
                }

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>