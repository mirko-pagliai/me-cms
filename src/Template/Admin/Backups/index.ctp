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
$this->assign('title', __d('me_cms', 'Database backups'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add'),
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Form->postButton(
    __d('me_cms', 'Delete all'),
    ['action' => 'delete-all'],
    ['class' => 'btn-danger', 'icon' => 'trash']
));
?>

<table class="table table-striped">
    <tr>
        <th><?= __d('me_cms', 'Filename') ?></th>
        <th class="min-width text-center"><?= __d('me_cms', 'Extension') ?></th>
        <th class="min-width text-center"><?= __d('me_cms', 'Compression') ?></th>
        <th class="min-width text-center"><?= __d('me_cms', 'Size') ?></th>
        <th class="min-width text-center"><?= __d('me_cms', 'Date') ?></th>
    </tr>
    <?php foreach ($backups as $backup) : ?>
        <tr>
            <td>
                <strong>
                    <?= $this->Html->link($backup->filename, ['action' => 'download', $backup->slug]) ?>
                </strong>
                <?php
                    $actions = [];
                    $actions[] = $this->Html->link(
                        __d('me_cms', 'Download'),
                        ['action' => 'download', $backup->slug],
                        ['icon' => 'download']
                    );
                    $actions[] = $this->Form->postLink(
                        __d('me_cms', 'Restore'),
                        ['action' => 'restore', $backup->slug],
                        [
                            'icon' => 'upload',
                            'confirm' => __d('me_cms', 'This will overwrite the current database and ' .
                                'some data may be lost. Are you sure?'),
                        ]
                    );
                    $actions[] = $this->Form->postLink(
                        __d('me_cms', 'Send'),
                        ['action' => 'send', $backup->slug],
                        [
                            'icon' => ' envelope-o',
                            'confirm' => __d('me_cms', 'The backup file will be sent by mail. Are you sure?'),
                        ]
                    );
                    $actions[] = $this->Form->postLink(
                        __d('me_cms', 'Delete'),
                        ['action' => 'delete', $backup->slug],
                        [
                            'class' => 'text-danger',
                            'icon' => 'trash-o',
                            'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                        ]
                    );

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </td>
            <td class="min-width text-center">
                <?= $backup->extension ?>
            </td>
            <td class="min-width text-center">
                <?= $backup->compression ?>
            </td>
            <td class="min-width text-center">
                <?= $this->Number->toReadableSize($backup->size) ?>
            </td>
            <td class="min-width text-center">
                <?= $backup->datetime->i18nFormat(getConfigOrFail('main.datetime.long')) ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>