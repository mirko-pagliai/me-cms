<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
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
                    <strong><?= $this->Html->link($position->title, ['action' => 'edit', $position->id]) ?></strong>
                    <?php
                        $actions = [
                            $this->Html->link(
                                __d('me_cms', 'Edit'),
                                ['action' => 'edit', $position->id],
                                ['icon' => 'pencil']
                            ),
                            $this->Form->postLink(
                                __d('me_cms', 'Delete'),
                                ['action' => 'delete', $position->id],
                                [
                                    'class' => 'text-danger',
                                    'icon' => 'trash-o',
                                    'confirm' => __d('me_cms', 'Are you sure you want to delete this?')
                                ]
                            ),
                            $this->Html->link(
                                __d('me_cms', 'Upload'),
                                [
                                    'controller' => 'Banners',
                                    'action' => 'upload',
                                    '?' => ['position' => $position->id]
                                ],
                                ['icon' => 'upload']
                            ),
                        ];

                        echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?= $position->description ?>
                </td>
                <td class="min-width text-center">
                    <?php if ($position->banner_count) : ?>
                        <?php
                            echo $this->Html->link(
                                $position->banner_count,
                                [
                                    'controller' => 'Banners',
                                    'action' => 'index',
                                    '?' => ['position' => $position->id]
                                ],
                                ['title' => __d('me_cms', 'View items that belong to this category')]
                            );
                        ?>
                    <?php else : ?>
                        <?= $position->banner_count ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->element('MeTools.paginator') ?>