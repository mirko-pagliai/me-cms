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
                    <?= I18N_ID ?> <code><?= $banner->id ?></code>
                </div>
                <div class="photo-album">
                    <?= I18N_POSITION ?>:
                    <?= $this->Html->link($banner->position->title, [
                        '?' => ['position' => $banner->position->id],
                    ], ['title' => I18N_BELONG_ELEMENT]) ?>
                </div>
                <div class="photo-created">
                    (<?= $banner->created->i18nFormat() ?>)
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
                    $this->Html->link(null, ['action' => 'edit', $banner->id], [
                        'icon' => 'pencil',
                        'title' => I18N_EDIT,
                    ]),
                ];

                if ($banner->target) {
                    $actions[] = $this->Html->link(null, $banner->target, [
                        'icon' => 'external-link',
                        'title' => I18N_OPEN,
                        'target' => '_blank',
                    ]);
                }

                $actions[] = $this->Html->link(null, ['action' => 'download', $banner->id], [
                    'icon' => 'download',
                    'title' => I18N_DOWNLOAD,
                ]);

                //Only admins can delete banners
                if ($this->Auth->isGroup('admin')) {
                    $actions[] = $this->Form->postLink(null, ['action' => 'delete', $banner->id], [
                        'class' => 'text-danger',
                        'icon' => 'trash-o',
                        'title' => I18N_DELETE,
                        'confirm' => I18N_SURE_TO_DELETE,
                    ]);
                }

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>