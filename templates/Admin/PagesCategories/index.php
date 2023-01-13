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
 * @var \MeCms\View\View\Admin\AppView $this
 */

$this->extend('MeCms./Admin/common/index');
$this->assign('title', __d('me_cms', 'Pages categories'));

$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add page'),
    ['controller' => 'Pages', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= I18N_ID ?></th>
            <th><?= I18N_TITLE ?></th>
            <th class="text-nowrap text-center"><?= __d('me_cms', 'Parent') ?></th>
            <th class="text-nowrap text-center"><?= I18N_PAGES ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category) : ?>
            <tr>
                <td class="text-nowrap text-center">
                    <code><?= $category->get('id') ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($category->get('title'), ['action' => 'edit', $category->get('id')]) ?>
                    </strong>
                    <?php
                    $actions = [
                        $this->Html->link(I18N_EDIT, ['action' => 'edit', $category->get('id')], ['icon' => 'pencil-alt']),
                    ];

                    //Only admins can delete pages categories
                    if ($this->Identity->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $category->get('id')], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    if ($category->get('page_count')) {
                        $actions[] = $this->Html->link(
                            I18N_OPEN,
                            ['_name' => 'pagesCategory', $category->get('slug')],
                            ['icon' => 'external-link-alt', 'target' => '_blank']
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-nowrap text-center">
                    <?php
                    if ($category->hasValue('parent') && $category->get('parent')->hasValue('title')) {
                        echo $category->get('parent')->get('title');
                    }
                    ?>
                </td>
                <td class="text-nowrap text-center">
                    <?php
                    if ($category->hasValue('page_count')) {
                        echo $this->Html->link(
                            (string)$category->get('page_count'),
                            ['controller' => 'Pages', 'action' => 'index', '?' => ['category' => $category->get('id')]],
                            ['title' => I18N_BELONG_ELEMENT]
                        );
                    } else {
                        echo $category->get('page_count');
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
