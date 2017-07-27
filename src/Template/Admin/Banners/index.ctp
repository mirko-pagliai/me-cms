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

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', __d('me_cms', 'ID')) ?></th>
            <th><?= $this->Paginator->sort('filename', __d('me_cms', 'Filename')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Positions.title', __d('me_cms', 'Position')) ?></th>
            <th class="text-center hidden-xs"><?= __d('me_cms', 'Url') ?></th>
            <th class="text-center"><?= __d('me_cms', 'Description') ?></th>
            <th class="text-center"><?= $this->Paginator->sort('click_count', __d('me_cms', 'Click')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('created', __d('me_cms', 'Date')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($banners as $banner) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $banner->id ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($banner->filename, ['action' => 'edit', $banner->id]) ?>
                    </strong>

                    <?php
                    //If the banner is not active (not published)
                    if (!$banner->active) {
                        echo $this->Html->span(
                            __d('me_cms', 'Not published'),
                            ['class' => 'record-label record-label-warning']
                        );
                    }

                    //If the banner is not displayed as a thumbnail
                    if (!$banner->thumbnail) {
                        echo $this->Html->span(
                            __d('me_cms', 'No thumbnail'),
                            ['class' => 'record-label record-label-warning']
                        );
                    }

                    $actions = [
                        $this->Html->link(
                            __d('me_cms', 'Edit'),
                            ['action' => 'edit', $banner->id],
                            ['icon' => 'pencil']
                        ),
                    ];

                    if ($banner->target) {
                        $actions[] = $this->Html->link(
                            __d('me_cms', 'Open'),
                            $banner->target,
                            ['icon' => 'external-link', 'target' => '_blank']
                        );
                    }

                    $actions[] = $this->Html->link(
                        __d('me_cms', 'Download'),
                        ['action' => 'download', $banner->id],
                        ['icon' => 'download']
                    );

                    //Only admins can delete banners
                    if ($this->Auth->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(
                            __d('me_cms', 'Delete'),
                            ['action' => 'delete', $banner->id],
                            [
                                'class' => 'text-danger',
                                'icon' => 'trash-o',
                                'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                            ]
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $this->Html->link(
                        $banner->position->title,
                        ['?' => ['position' => $banner->position->id]],
                        ['title' => __d('me_cms', 'View items that belong to this category')]
                    ) ?>
                </td>
                <td class="text-center hidden-xs">
                    <?php
                    if ($banner->target) {
                        $truncated = $this->Text->truncate($banner->target, 50, ['exact' => false]);
                        echo $this->Html->link($truncated, $banner->target, ['target' => '_blank']);
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?= $banner->description ?>
                </td>
                <td class="min-width text-center">
                    <?= $banner->click_count ?>
                </td>
                <td class="min-width text-center">
                    <div class="hidden-xs">
                        <?= $banner->created->i18nFormat(getConfigOrFail('main.datetime.long')) ?>
                    </div>
                    <div class="visible-xs">
                        <div><?= $banner->created->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $banner->created->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>