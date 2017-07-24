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
$this->assign('title', __d('me_cms', 'Banners positions'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add'),
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Upload banners'),
    ['controller' => 'Banners', 'action' => 'upload'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', __d('me_cms', 'ID')) ?></th>
            <th><?= $this->Paginator->sort('title', __d('me_cms', 'Title')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('description', __d('me_cms', 'Description')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('banner_count', __d('me_cms', 'Banners')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($positions as $position) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $position->id ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($position->title, ['action' => 'edit', $position->id]) ?>
                    </strong>
                    <?php
                        $actions = [];
                        $actions[] = $this->Html->link(
                            __d('me_cms', 'Edit'),
                            ['action' => 'edit', $position->id],
                            ['icon' => 'pencil']
                        );
                        $actions[] = $this->Form->postLink(
                            __d('me_cms', 'Delete'),
                            ['action' => 'delete', $position->id],
                            [
                                'class' => 'text-danger',
                                'icon' => 'trash-o',
                                'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                            ]
                        );
                        $actions[] = $this->Html->link(__d('me_cms', 'Upload'), [
                            'controller' => 'Banners',
                            'action' => 'upload',
                            '?' => ['position' => $position->id]
                        ], ['icon' => 'upload']);

                        echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $position->description ?>
                </td>
                <td class="min-width text-center">
                    <?php
                    if ($position->banner_count) {
                        echo $this->Html->link($position->banner_count, [
                            'controller' => 'Banners',
                            'action' => 'index',
                            '?' => ['position' => $position->id],
                        ], ['title' => __d('me_cms', 'View items that belong to this category')]);
                    } else {
                        echo $position->banner_count;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->element('MeTools.paginator') ?>