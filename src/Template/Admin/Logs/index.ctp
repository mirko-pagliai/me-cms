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
$this->assign('title', __d('me_cms', 'Logs'));

$this->append('actions', $this->Form->postButton(
    __d('me_cms', 'Clear all logs'),
    ['controller' => 'Systems', 'action' => 'tmpCleaner', 'logs'],
    ['class' => 'btn-danger', 'icon' => 'trash']
));
?>

<table class="table table-striped">
    <tr>
        <th><?= __d('me_cms', 'Filename') ?></th>
        <th class="text-center"><?= __d('me_cms', 'Size') ?></th>
    </tr>
    <?php foreach ($logs as $log) : ?>
        <tr>
            <td>
                <strong>
                    <?= $this->Html->link($log->filename, ['action' => 'view', $log->filename]) ?>
                </strong>
                <?php
                    $actions = [
                        $this->Html->link(
                            __d('me_cms', 'Basic view'),
                            ['action' => 'view', $log->filename],
                            ['icon' => 'eye']
                        ),
                    ];

                    if ($log->hasSerialized) {
                        $actions[] = $this->Html->link(
                            __d('me_cms', 'Advanced view'),
                            ['action' => 'viewSerialized', $log->filename],
                            ['icon' => 'eye']
                        );
                    }

                    $actions[] = $this->Html->link(
                        __d('me_cms', 'Download'),
                        ['action' => 'download', $log->filename],
                        ['icon' => 'download']
                    );
                    $actions[] = $this->Form->postLink(
                        __d('me_cms', 'Delete'),
                        ['action' => 'delete', $log->filename],
                        [
                            'class' => 'text-danger',
                            'icon' => 'trash-o',
                            'confirm' => __d('me_cms', 'Are you sure you want to delete this?')
                        ]
                    );

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </td>
            <td class="min-width text-center">
                <?= $this->Number->toReadableSize($log->size) ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>