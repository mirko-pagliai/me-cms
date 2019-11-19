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
$this->extend('/Admin/Common/BannersAndPhotos/index');
?>

<div class="row">
    <?php foreach ($banners as $banner) : ?>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item p-1 text-truncate text-center">
                        <?= $this->Html->link($banner->filename, ['action' => 'edit', $banner->id]) ?>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        <samp><?= I18N_ID ?> <?= $banner->id ?></samp>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        <?= I18N_POSITION ?>:
                        <?= $this->Html->link($banner->position->title, [
                            '?' => ['position' => $banner->position->id],
                        ], ['title' => I18N_BELONG_ELEMENT]) ?>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        (<?= $banner->created->i18nFormat() ?>)
                    </li>
                </ul>

                <?php
                $class = 'card-img-bottom';
                if ($banner->thumbnail) {
                    echo $this->Thumb->resize($banner->path, ['width' => 400], compact('class'));
                } else {
                    echo $this->Html->img($banner->www, compact('class'));
                }

                $actions = [
                    $this->Html->button(null, ['action' => 'edit', $banner->id], [
                        'class' => 'btn-link',
                        'icon' => 'pencil-alt',
                        'title' => I18N_EDIT,
                    ]),
                ];

                if ($banner->target) {
                    $actions[] = $this->Html->button(null, $banner->target, [
                        'class' => 'btn-link',
                        'icon' => 'external-link-alt',
                        'title' => I18N_OPEN,
                        'target' => '_blank',
                    ]);
                }

                $actions[] = $this->Html->button(null, ['action' => 'download', $banner->id], [
                    'class' => 'btn-link',
                    'icon' => 'download',
                    'title' => I18N_DOWNLOAD,
                ]);

                //Only admins can delete banners
                if ($this->Auth->isGroup('admin')) {
                    $actions[] = $this->Form->postButton(null, ['action' => 'delete', $banner->id], [
                        'class' => 'btn-link text-danger',
                        'icon' => 'trash-alt',
                        'title' => I18N_DELETE,
                        'confirm' => I18N_SURE_TO_DELETE,
                    ]);
                }
                ?>

                <div class="btn-toolbar mt-1 justify-content-center" role="toolbar">
                    <div class="btn-group" role="group">
                        <?= implode(PHP_EOL, $actions) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
