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
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Form->postButton(
    __d('me_cms', 'Delete all'),
    ['action' => 'delete-all'],
    ['class' => 'btn-danger', 'icon' => 'trash']
));
?>

<table class="table table-hover">
    <thead class="thead-default">
        <tr>
            <th><?= I18N_FILENAME ?></th>
            <th class="min-width text-center"><?= __d('me_cms', 'Extension') ?></th>
            <th class="min-width text-center"><?= __d('me_cms', 'Compression') ?></th>
            <th class="min-width text-center"><?= __d('me_cms', 'Size') ?></th>
            <th class="min-width text-center"><?= I18N_DATE ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($backups as $backup) : ?>
            <tr>
                <td>
                    <strong>
                        <?= $this->Html->link($backup->filename, ['action' => 'download', $backup->slug]) ?>
                    </strong>
                    <?php
                        $actions = [
                            $this->Html->link(
                                I18N_DOWNLOAD,
                                ['action' => 'download', $backup->slug],
                                ['icon' => 'download']
                            ),
                            $this->Form->postLink(
                                __d('me_cms', 'Restore'),
                                ['action' => 'restore', $backup->slug],
                                [
                                    'icon' => 'upload',
                                    'confirm' => __d('me_cms', 'This will overwrite the current database and ' .
                                        'some data may be lost. Are you sure?'),
                                ]
                            ),
                            $this->Form->postLink(
                                __d('me_cms', 'Send'),
                                ['action' => 'send', $backup->slug],
                                [
                                    'icon' => ' envelope-o',
                                    'confirm' => __d('me_cms', 'The backup file will be sent by mail. Are you sure?'),
                                ]
                            ),
                            $this->Form->postLink(
                                I18N_DELETE,
                                ['action' => 'delete', $backup->slug],
                                ['class' => 'text-danger', 'icon' => 'trash-o', 'confirm' => I18N_SURE_TO_DELETE]
                            ),
                        ];

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
    </tbody>
</table>