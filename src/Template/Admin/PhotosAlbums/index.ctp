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
$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Albums'));
$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Upload photos'),
    ['controller' => 'Photos', 'action' => 'upload'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', I18N_ID) ?></th>
            <th><?= $this->Paginator->sort('title', I18N_TITLE) ?></th>
            <th class="text-center"><?= I18N_DESCRIPTION ?></th>
            <th class="text-center"><?= $this->Paginator->sort('created', I18N_DATE) ?></th>
            <th class="text-nowrap text-center"><?= $this->Paginator->sort('photo_count', I18N_PHOTOS) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($albums as $album) : ?>
            <tr>
                <td class="text-nowrap text-center">
                    <code><?= $album->id ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($album->title, ['action' => 'edit', $album->id]) ?>
                    </strong>
                    <?php
                    $actions = [
                        $this->Html->link(I18N_EDIT, ['action' => 'edit', $album->id], ['icon' => 'pencil-alt']),
                    ];

                    //Only admins and managers can delete albums
                    if ($this->Auth->isGroup(['admin', 'manager'])) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $album->id], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    $actions[] = $this->Html->link(
                        I18N_UPLOAD,
                        ['controller' => 'Photos', 'action' => 'upload', '?' => ['album' => $album->id]],
                        ['icon' => 'upload']
                    );

                    if ($album->photo_count) {
                        $actions[] = $this->Html->link(
                            I18N_OPEN,
                            ['_name' => 'album', $album->slug],
                            ['icon' => 'external-link-alt', 'target' => '_blank']
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $album->description ?>
                </td>
                <td class="text-nowrap text-center">
                    <div class="d-none d-lg-block">
                        <?= $album->created->i18nFormat() ?>
                    </div>
                    <div class="d-lg-none">
                        <div><?= $album->created->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $album->created->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
                <td class="text-nowrap text-center">
                    <?php
                    if ($album->photo_count) {
                        echo $this->Html->link(
                            $album->photo_count,
                            ['controller' => 'Photos', 'action' => 'index', '?' => ['album' => $album->id]],
                            ['title' => I18N_BELONG_ELEMENT]
                        );
                    } else {
                        echo $album->photo_count;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('MeTools.paginator') ?>