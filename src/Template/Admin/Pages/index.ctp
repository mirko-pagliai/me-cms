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
$this->Library->datepicker('#created', ['format' => 'MM-YYYY', 'viewMode' => 'years']);
?>

<?= $this->Form->createInline(false, ['class' => 'filter-form', 'type' => 'get']) ?>
    <fieldset>
        <?= $this->Html->legend(I18N_FILTER, ['icon' => 'eye']) ?>
        <?php
        echo $this->Form->control('id', [
            'default' => $this->request->getQuery('id'),
            'placeholder' => I18N_ID,
            'size' => 2,
        ]);
        echo $this->Form->control('title', [
            'default' => $this->request->getQuery('title'),
            'placeholder' => I18N_TITLE,
            'size' => 16,
        ]);
        echo $this->Form->control('active', [
            'default' => $this->request->getQuery('active'),
            'empty' => I18N_ALL_STATUS,
            'options' => ['yes' => I18N_ONLY_PUBLISHED, 'no' => I18N_ONLY_NOT_PUBLISHED],
        ]);
        echo $this->Form->control('category', [
            'default' => $this->request->getQuery('category'),
            'empty' => sprintf('-- %s --', __d('me_cms', 'all categories')),
        ]);
        echo $this->Form->control('priority', [
            'default' => $this->request->getQuery('priority'),
            'empty' => sprintf('-- %s --', __d('me_cms', 'all priorities')),
        ]);
        echo $this->Form->datepicker('created', [
            'data-date-format' => 'YYYY-MM',
            'default' => $this->request->getQuery('created'),
            'placeholder' => __d('me_cms', 'month'),
            'size' => 5,
        ]);
        echo $this->Form->submit(null, ['icon' => 'search']);
        ?>
    </fieldset>
<?= $this->Form->end() ?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= $this->Paginator->sort('id', I18N_ID) ?></th>
            <th><?= $this->Paginator->sort('title', I18N_TITLE) ?></th>
            <th class="text-center"><?= $this->Paginator->sort('Categories.title', I18N_CATEGORY) ?></th>
            <th class="min-width text-center"><?= $this->Paginator->sort('priority', I18N_PRIORITY) ?></th>
            <th class="min-width text-center"><?= $this->Paginator->sort('created', I18N_DATE) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page) : ?>
            <tr>
                <td class="min-width text-center">
                    <code><?= $page->id ?></code>
                </td>
                <td>
                    <strong><?= $this->Html->link($page->title, ['action' => 'edit', $page->id]) ?></strong>
                    <?php
                    $class = 'record-label record-label-warning';

                    //If the page is not active (it's a draft)
                    if (!$page->active) {
                        echo $this->Html->span(I18N_DRAFT, compact('class'));
                    }

                    //If the page is scheduled
                    if ($page->created->isFuture()) {
                        echo $this->Html->span(I18N_SCHEDULED, compact('class'));
                    }

                    $actions = [];

                    //Only admins and managers can edit pages
                    if ($this->Auth->isGroup(['admin', 'manager'])) {
                        $actions[] = $this->Html->link(
                            I18N_EDIT,
                            ['action' => 'edit', $page->id],
                            ['icon' => 'pencil']
                        );
                    }

                    //Only admins can delete pages
                    if ($this->Auth->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $page->id], [
                            'class' => 'text-danger',
                            'icon' => 'trash-o',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    //If the page is active and is not scheduled
                    if ($page->active && !$page->created->isFuture()) {
                        $actions[] = $this->Html->link(I18N_OPEN, ['_name' => 'page', $page->slug], [
                            'icon' => 'external-link',
                            'target' => '_blank',
                        ]);
                    } else {
                        $actions[] = $this->Html->link(I18N_PREVIEW, ['_name' => 'pagesPreview', $page->slug], [
                            'icon' => 'external-link',
                            'target' => '_blank',
                        ]);
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="min-width text-center">
                    <?= $this->Html->link(
                        $page->category->title,
                        ['?' => ['category' => $page->category->id]],
                        ['title' => I18N_BELONG_ELEMENT]
                    ) ?>
                </td>
                <td class="min-width text-center">
                    <?php
                    switch ($page->priority) {
                        case '1':
                            $priority = 1;
                            $class = 'priority-verylow';
                            $tooltip = __d('me_cms', 'Very low');
                            break;
                        case '2':
                            $priority = 2;
                            $class = 'priority-low';
                            $tooltip = __d('me_cms', 'Low');
                            break;
                        case '4':
                            $priority = 4;
                            $class = 'priority-high';
                            $tooltip = __d('me_cms', 'High');
                            break;
                        case '5':
                            $priority = 5;
                            $class = 'priority-veryhigh';
                            $tooltip = __d('me_cms', 'Very high');
                            break;
                        default:
                            $priority = 3;
                            $class = 'priority-normal';
                            $tooltip = __d('me_cms', 'Normal');
                            break;
                    }

                    echo $this->Html->badge($priority, compact('class', 'tooltip'));
                    ?>
                </td>
                <td class="min-width text-center">
                    <div class="hidden-xs">
                        <?= $page->created->i18nFormat(getConfigOrFail('main.datetime.long')) ?>
                    </div>
                    <div class="visible-xs">
                        <div><?= $page->created->i18nFormat(getConfigOrFail('main.date.short')) ?></div>
                        <div><?= $page->created->i18nFormat(getConfigOrFail('main.time.short')) ?></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('MeTools.paginator') ?>