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

<div class="row">
    <?php foreach ($photos as $photo) : ?>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item p-1 text-truncate text-center">
                        <?= $this->Html->link($photo->filename, ['action' => 'edit', $photo->id]) ?>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        <samp><?= I18N_ID ?> <?= $photo->id ?></samp>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        <?= __d('me_cms', 'Album') ?>:
                        <?= $this->Html->link(
                            $photo->album->title,
                            ['?' => ['album' => $photo->album->id]],
                            ['title' => I18N_BELONG_ELEMENT]
                        ) ?>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        (<?= $photo->created->i18nFormat() ?>)
                    </li>
                </ul>

                <?php
                echo $this->Thumb->fit($photo->path, ['width' => 400], ['class' => 'card-img-bottom']);

                $actions = [
                    $this->Html->button(null, ['action' => 'edit', $photo->id], [
                        'icon' => 'pencil',
                        'title' => I18N_EDIT,
                    ]),
                    $this->Html->button(null, ['action' => 'download', $photo->id], [
                        'icon' => 'download',
                        'title' => I18N_DOWNLOAD,
                    ]),
                ];

                //Only admins and managers can delete photos
                if ($this->Auth->isGroup(['admin', 'manager'])) {
                    $actions[] = $this->Form->postButton(null, ['action' => 'delete', $photo->id], [
                        'class' => 'text-danger',
                        'icon' => 'trash-o',
                        'title' => I18N_DELETE,
                        'confirm' => I18N_SURE_TO_DELETE,
                    ]);
                }

                //If the photo is active
                if ($photo->active) {
                    $actions[] = $this->Html->button(
                        null,
                        ['_name' => 'photo', 'slug' => $photo->album->slug, 'id' => $photo->id],
                        ['icon' => 'external-link', 'target' => '_blank', 'title' => I18N_OPEN]
                    );
                } else {
                    $actions[] = $this->Html->button(null, ['_name' => 'photosPreview', $photo->id], [
                        'icon' => 'external-link',
                        'target' => '_blank',
                        'title' => I18N_PREVIEW,
                    ]);
                }
                ?>

                <div class="btn-toolbar justify-content-center" role="toolbar">
                    <div class="btn-group" role="group">
                        <?= implode(PHP_EOL, $actions) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>