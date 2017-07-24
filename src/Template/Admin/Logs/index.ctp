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
                <strong><?= $this->Html->link($log->filename, ['action' => 'view', $log->filename]) ?></strong>
                <?php
                $actions = [];

                $actions[] = $this->Html->link(
                    __d('me_cms', 'Basic view'),
                    ['action' => 'view', $log->filename],
                    ['icon' => 'eye']
                );

                if ($log->hasSerialized) {
                    $actions[] = $this->Html->link(
                        __d('me_cms', 'Advanced view'),
                        ['action' => 'view', $log->filename, '?' => ['as' => 'serialized']],
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