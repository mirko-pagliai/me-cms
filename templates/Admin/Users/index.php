<?php /** @noinspection PhpUnhandledExceptionInspection */
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
 *
 * @var \MeCms\View\View\Admin\AppView $this
 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\User> $users
 */

$this->extend('MeCms./Admin/common/index');
$this->assign('title', I18N_USERS);
$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add group'),
    ['controller' => 'UsersGroups', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));

echo $this->Form->createInline(null, ['class' => 'filter-form', 'type' => 'get']);
echo $this->Html->legend(I18N_FILTER, ['icon' => 'eye']);
echo $this->Form->control('id', [
    'default' => $this->getRequest()->getQuery('id'),
    'placeholder' => I18N_ID,
    'size' => 1,
]);
echo $this->Form->control('username', [
    'default' => $this->getRequest()->getQuery('username'),
    'placeholder' => __d('me_cms', 'username'),
    'size' => 13,
]);
echo $this->Form->control('status', [
    'default' => $this->getRequest()->getQuery('status'),
    'empty' => I18N_ALL_STATUS,
    'options' => [
        'active' => __d('me_cms', 'Only active'),
        'pending' => __d('me_cms', 'Only pending'),
        'banned' => __d('me_cms', 'Only banned'),
    ],
]);
echo $this->Form->control('group', [
    'default' => $this->getRequest()->getQuery('group'),
    'empty' => sprintf('-- %s --', I18N_ALL_VALUES),
]);
echo $this->Form->control('created', [
    'default' => $this->getRequest()->getQuery('created'),
    'placeholder' => __d('me_cms', 'month'),
    'size' => 3,
    'type' => 'month',
]);
echo $this->Form->submit(null, ['icon' => 'search']);
echo $this->Form->end();
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', I18N_ID) ?></th>
            <th><?php echo $this->Paginator->sort('username', I18N_USERNAME) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('first_name', I18N_NAME) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('email', I18N_EMAIL) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Groups.label', I18N_GROUP) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('post_count', I18N_POSTS) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('created', I18N_DATE) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td class="text-nowrap text-center align-middle">
                    <code><?= $user->get('id') ?></code>
                </td>
                <td>
                    <?php
                    echo $this->Html->link($user->get('username'), ['action' => 'view', $user->get('id')], ['class' => 'fw-bold']);

                    $class = 'record-badge badge badge-danger';

                    //If it's banned
                    if ($user->get('banned')) {
                        echo $this->Html->span(__d('me_cms', 'Banned'), compact('class'));
                    //Else, if it's not active (pending)
                    } elseif (!$user->get('active')) {
                        echo $this->Html->span(__d('me_cms', 'Pending'), compact('class'));
                    }

                    $actions = [
                        $this->Html->link(__d('me_cms', 'View'), ['action' => 'view', $user->get('id')], ['icon' => 'eye']),
                        $this->Html->link(I18N_EDIT, ['action' => 'edit', $user->get('id')], ['icon' => 'pencil-alt']),
                    ];

                    //Only admins can activate accounts and delete users
                    if ($this->Identity->isGroup('admin')) {
                        //If it's not active (pending)
                        if (!$user->get('active')) {
                            $actions[] = $this->Form->postLink(__d('me_cms', 'Activate'), ['action' => 'activate', $user->get('id')], [
                                'icon' => 'user-plus',
                                'confirm' => __d('me_cms', 'Are you sure you want to activate this account?'),
                            ]);
                        }

                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $user->get('id')], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-center align-middle">
                    <?= $user->get('full_name') ?>
                </td>
                <td class="text-center align-middle">
                    <?= $this->Html->link($user->get('email'), sprintf('mailto:%s', $user->get('email'))) ?>
                </td>
                <td class="text-center align-middle">
                    <?= $this->Html->link(
                        $user->get('group')->get('label'),
                        ['?' => ['group' => $user->get('group')->get('id')]],
                        ['title' => I18N_BELONG_ELEMENT]
                    ) ?>
                </td>
                <td class="text-nowrap text-center align-middle">
                    <?php
                    if ($user->hasValue('post_count')) {
                        echo $this->Html->link(
                            (string)$user->get('post_count'),
                            ['controller' => 'Posts', 'action' => 'index', '?' => ['user' => $user->get('id')]],
                            ['title' => I18N_BELONG_USER]
                        );
                    } else {
                        echo $user->get('post_count');
                    }
                    ?>
                </td>
                <td class="text-nowrap text-center align-middle">
                    <div class="d-none d-lg-block">
                        <?= $user->get('created')->i18nFormat() ?>
                    </div>
                    <div class="d-lg-none">
                        <div><?= $user->get('created')->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $user->get('created')->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('MeTools.paginator') ?>
