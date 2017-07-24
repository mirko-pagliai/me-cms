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