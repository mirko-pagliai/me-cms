<?php
declare(strict_types=1);

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
$this->extend('MeCms./Admin/common/index');
$this->assign('title', __d('me_cms', 'Users groups'));
$this->append('actions', $this->Html->button(
    I18N_ADD,
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
            <th class="text-center"><?= $this->Paginator->sort('id', I18N_ID) ?></th>
            <th><?= $this->Paginator->sort('name', I18N_NAME) ?></th>
            <th><?= $this->Paginator->sort('label', I18N_LABEL) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('user_count', I18N_USERS) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($groups as $group) : ?>
            <tr>
                <td class="text-nowrap text-center">
                    <code><?= $group->get('id') ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($group->get('name'), ['action' => 'edit', $group->get('id')]) ?>
                    </strong>
                    <?php
                    $actions = [
                        $this->Html->link(I18N_EDIT, ['action' => 'edit', $group->get('id')], ['icon' => 'pencil-alt']),
                        $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $group->get('id')], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]),
                    ];

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td>
                    <?= $group->get('description') ?>
                </td>
                <td class="text-nowrap text-center">
                    <?php
                    if ($group->get('user_count')) {
                        echo $this->Html->link(
                            (string)$group->get('user_count'),
                            ['controller' => 'Users', 'action' => 'index', '?' => ['group' => $group->get('id')]],
                            ['title' => I18N_BELONG_ELEMENT]
                        );
                    } else {
                        echo $group->get('user_count');
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('MeTools.paginator') ?>
