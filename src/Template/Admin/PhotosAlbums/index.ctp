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
$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Albums'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add'),
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
            <th class="text-center"><?= $this->Paginator->sort('id', __d('me_cms', 'ID')) ?></th>
            <th><?= $this->Paginator->sort('title', __d('me_cms', 'Title')) ?></th>
            <th class="text-center"><?= __d('me_cms', 'Description') ?></th>
            <th class="min-width text-center"><?= $this->Paginator->sort('photo_count', __d('me_cms', 'Photos')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($albums as $album) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $album->id ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($album->title, ['action' => 'edit', $album->id]) ?>
                    </strong>
                    <?php
                    $actions = [
                        $this->Html->link(
                            __d('me_cms', 'Edit'),
                            ['action' => 'edit', $album->id],
                            ['icon' => 'pencil']
                        ),
                    ];

                    //Only admins and managers can delete albums
                    if ($this->Auth->isGroup(['admin', 'manager'])) {
                        $actions[] = $this->Form->postLink(
                            __d('me_cms', 'Delete'),
                            ['action' => 'delete', $album->id],
                            [
                                'class' => 'text-danger',
                                'icon' => 'trash-o',
                                'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                            ]
                        );
                    }

                    $actions[] = $this->Html->link(__d('me_cms', 'Upload'), [
                        'controller' => 'Photos',
                        'action' => 'upload',
                        '?' => ['album' => $album->id],
                    ], ['icon' => 'upload']);

                    if ($album->photo_count) {
                        $actions[] = $this->Html->link(
                            __d('me_cms', 'Open'),
                            ['_name' => 'album', $album->slug],
                            ['icon' => 'external-link', 'target' => '_blank']
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $album->description ?>
                </td>
                <td class="min-width text-center">
                    <?php
                    if ($album->photo_count) {
                        echo $this->Html->link($album->photo_count, [
                            'controller' => 'Photos',
                            'action' => 'index',
                            '?' => ['album' => $album->id],
                        ], ['title' => __d('me_cms', 'View items that belong to this category')]);
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