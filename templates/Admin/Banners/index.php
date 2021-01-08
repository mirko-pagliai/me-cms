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

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', I18N_ID) ?></th>
            <th><?= $this->Paginator->sort('filename', I18N_FILENAME) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Positions.title', I18N_POSITION) ?></th>
            <th class="text-center d-none d-lg-block"><?= __d('me_cms', 'Url') ?></th>
            <th class="text-center"><?= I18N_DESCRIPTION ?></th>
            <th class="text-center"><?= $this->Paginator->sort('click_count', __d('me_cms', 'Click')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('created', I18N_DATE) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($banners as $banner) : ?>
            <tr>
                <td class="text-nowrap text-center">
                    <code><?= $banner->get('id') ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($banner->get('filename'), ['action' => 'edit', $banner->get('id')]) ?>
                    </strong>

                    <?php
                    $class = 'record-badge badge badge-warning';

                    //If the banner is not active (not published)
                    if (!$banner->get('active')) {
                        echo $this->Html->span(I18N_NOT_PUBLISHED, compact('class'));
                    }

                    //If the banner is not displayed as a thumbnail
                    if (!$banner->has('thumbnail')) {
                        echo $this->Html->span(__d('me_cms', 'No thumbnail'), compact('class'));
                    }

                    $actions = [
                        $this->Html->link(I18N_EDIT, ['action' => 'edit', $banner->get('id')], ['icon' => 'pencil-alt']),
                    ];

                    if ($banner->get('target')) {
                        $actions[] = $this->Html->link(I18N_OPEN, $banner->get('target'), [
                            'icon' => 'external-link-alt',
                            'target' => '_blank',
                        ]);
                    }

                    $actions[] = $this->Html->link(
                        I18N_DOWNLOAD,
                        ['action' => 'download', $banner->get('id')],
                        ['icon' => 'download']
                    );

                    //Only admins can delete banners
                    if ($this->Auth->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $banner->get('id')], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $this->Html->link($banner->get('position')->get('title'), [
                        '?' => ['position' => $banner->get('position')->get('id')],
                    ], ['title' => I18N_BELONG_ELEMENT]) ?>
                </td>
                <td class="text-center d-none d-lg-block">
                    <?php
                    if ($banner->get('target')) {
                        $truncated = $this->Text->truncate($banner->get('target'), 50, ['exact' => false]);
                        echo $this->Html->link($truncated, $banner->get('target'), ['target' => '_blank']);
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?= $banner->get('description') ?>
                </td>
                <td class="text-nowrap text-center">
                    <?= $banner->get('click_count') ?>
                </td>
                <td class="text-nowrap text-center">
                    <div class="d-none d-lg-block">
                        <?= $banner->get('created')->i18nFormat() ?>
                    </div>
                    <div class="d-lg-none">
                        <div><?= $banner->get('created')->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $banner->get('created')->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
