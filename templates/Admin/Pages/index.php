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
 *
 * @var \MeCms\Model\Entity\Page[] $pages
 * @var \MeCms\View\View\Admin\AppView $this
 */
$this->extend('MeCms./Admin/common/index');
$this->assign('title', I18N_PAGES);
$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add category'),
    ['controller' => 'PagesCategories', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));

echo $this->Form->createInline(null, ['class' => 'filter-form', 'type' => 'get']);
echo $this->Html->legend(I18N_FILTER, ['icon' => 'eye']);
echo $this->Form->control('id', [
    'default' => $this->getRequest()->getQuery('id'),
    'placeholder' => I18N_ID,
    'size' => 1,
]);
echo $this->Form->control('title', [
    'default' => $this->getRequest()->getQuery('title'),
    'placeholder' => I18N_TITLE,
    'size' => 13,
]);
echo $this->Form->control('active', [
    'default' => $this->getRequest()->getQuery('active'),
    'empty' => I18N_ALL_STATUS,
    'options' => [I18N_YES => I18N_ONLY_PUBLISHED, I18N_NO => I18N_ONLY_NOT_PUBLISHED],
]);
echo $this->Form->control('category', [
    'default' => $this->getRequest()->getQuery('category'),
    'empty' => sprintf('-- %s --', I18N_ALL_VALUES),
]);
echo $this->Form->control('priority', [
    'default' => $this->getRequest()->getQuery('priority'),
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
            <th><?= $this->Paginator->sort('title', I18N_TITLE) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Categories.title', I18N_CATEGORY) ?></th>
            <th class="text-nowrap text-center"><?= $this->Paginator->sort('priority', I18N_PRIORITY) ?></th>
            <th class="text-nowrap text-center"><?= $this->Paginator->sort('created', I18N_DATE) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page) : ?>
            <tr>
                <td class="text-nowrap text-center align-middle">
                    <code><?= $page->get('id') ?></code>
                </td>
                <td>
                    <?php
                    echo $this->Html->link($page->get('title'), ['action' => 'edit', $page->get('id')], ['class' => 'fw-bold']);

                    $class = 'record-badge badge badge-warning';

                    //If it's not active (it's a draft)
                    if (!$page->get('active')) {
                        echo $this->Html->span(I18N_DRAFT, compact('class'));
                    }

                    //If it's scheduled
                    if ($page->get('created')->isFuture()) {
                        echo $this->Html->span(I18N_SCHEDULED, compact('class'));
                    }

                    $actions = [];

                    //Only admins and managers can edit
                    if ($this->Identity->isGroup('admin', 'manager')) {
                        $actions[] = $this->Html->link(I18N_EDIT, ['action' => 'edit', $page->get('id')], ['icon' => 'pencil-alt']);
                    }

                    //Only admins can delete
                    if ($this->Identity->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $page->get('id')], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    //If it's active and is not scheduled
                    if ($page->get('active') && !$page->get('created')->isFuture()) {
                        $actions[] = $this->Html->link(I18N_OPEN, ['_name' => 'page', $page->get('slug')], [
                            'icon' => 'external-link-alt',
                            'target' => '_blank',
                        ]);
                    } else {
                        $actions[] = $this->Html->link(I18N_PREVIEW, ['_name' => 'pagesPreview', $page->get('slug')], [
                            'icon' => 'external-link-alt',
                            'target' => '_blank',
                        ]);
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-nowrap text-center align-middle">
                    <?= $this->Html->link(
                        $page->get('category')->get('title'),
                        ['?' => ['category' => $page->get('category')->get('id')]],
                        ['title' => I18N_BELONG_ELEMENT]
                    ) ?>
                </td>

                <td class="text-nowrap text-center align-middle">
                    <?= $this->element('admin/priority-badge', ['priority' => $page->get('priority')]) ?>
                </td>
                <td class="text-nowrap text-center align-middle">
                    <div class="d-none d-lg-block">
                        <?= $page->get('created')->i18nFormat() ?>
                    </div>
                    <div class="d-lg-none">
                        <div><?= $page->get('created')->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $page->get('created')->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('MeTools.paginator') ?>
