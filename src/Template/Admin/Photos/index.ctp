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

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', __d('me_cms', 'ID')) ?></th>
            <th><?= $this->Paginator->sort('filename', __d('me_cms', 'Filename')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Albums.title', __d('me_cms', 'Album')) ?></th>
            <th class="text-center"><?= __d('me_cms', 'Description') ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Photos.created', __d('me_cms', 'Date')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($photos as $photo) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $photo->id ?></code>
                </td>
                <td>
                    <strong><?= $this->Html->link($photo->filename, ['action' => 'edit', $photo->id]) ?></strong>
                    <?php
                    //If the photo is not active (not published)
                    if (!$photo->active) {
                        echo $this->Html->span(
                            __d('me_cms', 'Not published'),
                            ['class' => 'record-label record-label-warning']
                        );
                    }

                    $actions = [];
                    $actions[] = $this->Html->link(
                        __d('me_cms', 'Edit'),
                        ['action' => 'edit', $photo->id],
                        ['icon' => 'pencil']
                    );
                    $actions[] = $this->Html->link(
                        __d('me_cms', 'Download'),
                        ['action' => 'download', $photo->id],
                        ['icon' => 'download']
                    );

                    //Only admins and managers can delete photos
                    if ($this->Auth->isGroup(['admin', 'manager'])) {
                        $actions[] = $this->Form->postLink(
                            __d('me_cms', 'Delete'),
                            ['action' => 'delete', $photo->id],
                            [
                                'class' => 'text-danger',
                                'icon' => 'trash-o',
                                'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                            ]
                        );
                    }

                    //If the photo is active
                    if ($photo->active) {
                        $actions[] = $this->Html->link(__d('me_cms', 'Open'), [
                            '_name' => 'photo',
                            'slug' => $photo->album->slug,
                            'id' => $photo->id
                        ], ['icon' => 'external-link', 'target' => '_blank']);
                    } else {
                        $actions[] = $this->Html->link(
                            __d('me_cms', 'Preview'),
                            ['_name' => 'photosPreview', $photo->id],
                            ['icon' => 'external-link', 'target' => '_blank']
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $this->Html->link(
                        $photo->album->title,
                        ['?' => ['album' => $photo->album->id]],
                        ['title' => __d('me_cms', 'View items that belong to this category')]
                    ) ?>
                </td>
                <td class="text-center">
                    <?= $photo->description ?>
                </td>
                <td class="min-width text-center">
                    <div class="hidden-xs">
                        <?= $photo->created->i18nFormat(getConfigOrFail('main.datetime.long')) ?>
                    </div>
                    <div class="visible-xs">
                        <div><?= $photo->created->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $photo->created->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>