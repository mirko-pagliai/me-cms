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

$this->extend('/Admin/Common/Banners/index');
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', __d('me_cms', 'ID')) ?></th>
            <th><?= $this->Paginator->sort('filename', __d('me_cms', 'Filename')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Positions.name', __d('me_cms', 'Position')) ?></th>
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
                        <?php
                            echo $this->Html->link(
                                $banner->filename,
                                ['action' => 'edit', $banner->id]
                            );
                        ?>
                    </strong>
                    
                    <?php
                    //If the banner is not active (not published)
                    if (!$banner->active) {
                        echo $this->Html->span(
                            __d('me_cms', 'Not published'),
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
                                'confirm' => __d('me_cms', 'Are you sure you want to delete this?')
                            ]
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center">
                    <?php
                        echo $this->Html->link(
                            $banner->position->name,
                            ['?' => ['position' => $banner->position->id]],
                            ['title' => __d('me_cms', 'View items that belong to this category')]
                        );
                    ?>
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
                        <?= $banner->created->i18nFormat(config('main.datetime.long')) ?>
                    </div>
                    <div class="visible-xs">
                        <div><?= $banner->created->i18nFormat(config('main.date.short')) ?></div>
                        <div><?= $banner->created->i18nFormat(config('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>