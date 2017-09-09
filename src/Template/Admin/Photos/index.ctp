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
            <th class="text-center"><?= $this->Paginator->sort('id', I18N_ID) ?></th>
            <th><?= $this->Paginator->sort('filename', I18N_FILENAME) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Albums.title', __d('me_cms', 'Album')) ?></th>
            <th class="text-center"><?= I18N_DESCRIPTION ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Photos.created', I18N_DATE) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($photos as $photo) : ?>
            <tr>
                <td class="text-nowrap text-center">
                    <code><?= $photo->id ?></code>
                </td>
                <td>
                    <strong><?= $this->Html->link($photo->filename, ['action' => 'edit', $photo->id]) ?></strong>
                    <?php
                    //If the photo is not active (not published)
                    if (!$photo->active) {
                        echo $this->Html->span(
                            I18N_NOT_PUBLISHED,
                            ['class' => 'record-badge badge badge-warning']
                        );
                    }

                    $actions = [
                        $this->Html->link(
                            I18N_EDIT,
                            ['action' => 'edit', $photo->id],
                            ['icon' => 'pencil']
                        ),
                        $this->Html->link(
                            I18N_DOWNLOAD,
                            ['action' => 'download', $photo->id],
                            ['icon' => 'download']
                        ),
                    ];

                    //Only admins and managers can delete photos
                    if ($this->Auth->isGroup(['admin', 'manager'])) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $photo->id], [
                            'class' => 'text-danger',
                            'icon' => 'trash-o',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    //If the photo is active
                    if ($photo->active) {
                        $actions[] = $this->Html->link(I18N_OPEN, [
                            '_name' => 'photo',
                            'slug' => $photo->album->slug,
                            'id' => $photo->id
                        ], ['icon' => 'external-link', 'target' => '_blank']);
                    } else {
                        $actions[] = $this->Html->link(
                            I18N_PREVIEW,
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
                        ['title' => I18N_BELONG_ELEMENT]
                    ) ?>
                </td>
                <td class="text-center">
                    <?= $photo->description ?>
                </td>
                <td class="text-nowrap text-center">
                    <div class="d-none d-lg-block">
                        <?= $photo->created->i18nFormat() ?>
                    </div>
                    <div class="d-lg-none">
                        <div><?= $photo->created->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $photo->created->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>