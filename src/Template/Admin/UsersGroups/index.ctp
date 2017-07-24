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
$this->assign('title', __d('me_cms', 'Users groups'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add'),
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add user'),
    ['controller' => 'Users', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', __d('me_cms', 'ID')) ?></th>
            <th><?= $this->Paginator->sort('name', __d('me_cms', 'Name')) ?></th>
            <th><?= $this->Paginator->sort('label', __d('me_cms', 'Label')) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('user_count', __d('me_cms', 'Users')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($groups as $group) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $group->id ?></code>
                </td>
                <td>
                    <strong><?= $this->Html->link($group->name, ['action' => 'edit', $group->id]) ?></strong>
                    <?php
                        $actions = [];
                        $actions[] = $this->Html->link(
                            __d('me_cms', 'Edit'),
                            ['action' => 'edit', $group->id],
                            ['icon' => 'pencil']
                        );
                        $actions[] = $this->Form->postLink(
                            __d('me_cms', 'Delete'),
                            ['action' => 'delete', $group->id],
                            [
                                'class' => 'text-danger',
                                'icon' => 'trash-o',
                                'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
                            ]
                        );

                        echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td>
                    <?= $group->description ?>
                </td>
                <td class="min-width text-center">
                    <?php
                    if ($group->user_count) {
                        echo $this->Html->link($group->user_count, [
                            'controller' => 'Users',
                            'action' => 'index',
                            '?' => ['group' => $group->id],
                        ], ['title' => __d('me_cms', 'View items that belong to this category')]);
                    } else {
                        echo $group->user_count;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('MeTools.paginator') ?>