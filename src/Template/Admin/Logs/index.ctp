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
    <thead>
        <tr>
            <th><?= I18N_FILENAME ?></th>
            <th class="text-center"><?= __d('me_cms', 'Size') ?></th>
        </tr>
    </thead>
    <tbody>
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
                        ['action' => 'view', $log->filename, '?' => ['as' => 'serialized']],
                        ['icon' => 'eye']
                    );
                }

                $actions[] = $this->Html->link(
                    I18N_DOWNLOAD,
                    ['action' => 'download', $log->filename],
                    ['icon' => 'download']
                );
                $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $log->filename], [
                    'class' => 'text-danger',
                    'icon' => 'trash-o',
                    'confirm' => I18N_SURE_TO_DELETE,
                ]);

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </td>
            <td class="text-nowrap text-center">
                <?= $this->Number->toReadableSize($log->size) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>