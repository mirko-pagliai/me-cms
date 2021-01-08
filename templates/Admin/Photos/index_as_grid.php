<?php
declare(strict_types=1);

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
$this->extend('/Admin/common/BannersAndPhotos/index');
?>

<div class="row">
    <?php foreach ($photos as $photo) : ?>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item p-1 text-truncate text-center">
                        <?= $this->Html->link($photo->get('filename'), ['action' => 'edit', $photo->get('id')]) ?>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        <samp><?= I18N_ID ?> <?= $photo->get('id') ?></samp>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        <?= __d('me_cms', 'Album') ?>:
                        <?= $this->Html->link(
                            $photo->get('album')->get('title'),
                            ['?' => ['album' => $photo->get('album')->get('id')]],
                            ['title' => I18N_BELONG_ELEMENT]
                        ) ?>
                    </li>
                    <li class="list-group-item p-1 small text-center">
                        (<?= $photo->get('created')->i18nFormat() ?>)
                    </li>
                </ul>

                <?php
                echo $this->Thumb->fit($photo->get('path'), ['width' => 400], ['class' => 'card-img-bottom']);

                $actions = [];

                //If Fancybox is enabled, adds the preview action
                if (getConfig('default.fancybox')) {
                    $actions[] = $this->Html->button(null, ['action' => 'edit', $photo->get('id')], [
                        'class' => 'btn-link fancybox',
                        'icon' => 'search',
                        'title' => I18N_PREVIEW,
                        'data-fancybox-href' => $this->Thumb->resizeUrl($photo->get('path'), ['height' => 1280]),
                    ]);
                }

                $actions[] = $this->Html->button(null, ['action' => 'edit', $photo->get('id')], [
                    'class' => 'btn-link',
                    'icon' => 'pencil-alt',
                    'title' => I18N_EDIT,
                ]);
                $actions[] = $this->Html->button(null, ['action' => 'download', $photo->get('id')], [
                    'class' => 'btn-link',
                    'icon' => 'download',
                    'title' => I18N_DOWNLOAD,
                ]);

                //Only admins and managers can delete photos
                if ($this->Auth->isGroup(['admin', 'manager'])) {
                    $actions[] = $this->Form->postButton(null, ['action' => 'delete', (string)$photo->get('id')], [
                        'class' => 'btn-link text-danger',
                        'icon' => 'trash-alt',
                        'title' => I18N_DELETE,
                        'confirm' => I18N_SURE_TO_DELETE,
                    ]);
                }

                //If the photo is active
                if ($photo->get('active')) {
                    $actions[] = $this->Html->button(null, [
                        '_name' => 'photo',
                        'slug' => $photo->get('album')->get('slug'),
                        'id' => (string)$photo->get('id'),
                    ], [
                        'class' => 'btn-link',
                        'icon' => 'external-link-alt',
                        'target' => '_blank',
                        'title' => I18N_OPEN,
                    ]);
                } else {
                    $actions[] = $this->Html->button(null, ['_name' => 'photosPreview', $photo->get('id')], [
                        'class' => 'btn-link',
                        'icon' => 'external-link-alt',
                        'target' => '_blank',
                        'title' => I18N_PREVIEW,
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
