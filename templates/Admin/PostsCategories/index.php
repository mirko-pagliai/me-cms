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
$this->extend('/Admin/Common/index');
$this->assign('title', I18N_POSTS_CATEGORIES);
$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add post'),
    ['controller' => 'Posts', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center"><?= I18N_ID ?></th>
            <th><?= I18N_TITLE ?></th>
            <th class="text-nowrap text-center"><?= __d('me_cms', 'Parent') ?></th>
            <th class="text-nowrap text-center"><?= I18N_POSTS ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category) : ?>
            <tr>
                <td class="text-nowrap text-center">
                    <code><?= $category->id ?></code>
                </td>
                <td>
                    <strong>
                        <?= $this->Html->link($category->title, ['action' => 'edit', $category->id]) ?>
                    </strong>

                    <?php
                    $actions = [
                        $this->Html->link(I18N_EDIT, ['action' => 'edit', $category->id], ['icon' => 'pencil-alt']),
                    ];

                    //Only admins can delete posts categories
                    if ($this->Auth->isGroup('admin')) {
                        $actions[] = $this->Form->postLink(I18N_DELETE, ['action' => 'delete', $category->id], [
                            'class' => 'text-danger',
                            'icon' => 'trash-alt',
                            'confirm' => I18N_SURE_TO_DELETE,
                        ]);
                    }

                    if ($category->post_count) {
                        $actions[] = $this->Html->link(
                            I18N_OPEN,
                            ['_name' => 'postsCategory', $category->slug],
                            ['icon' => 'external-link-alt', 'target' => '_blank']
                        );
                    }

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                    ?>
                </td>
                <td class="text-nowrap text-center">
                    <?php
                    if (!empty($category->parent->title)) {
                        echo $category->parent->title;
                    }
                    ?>
                </td>
                <td class="text-nowrap text-center">
                    <?php
                    if ($category->post_count) {
                        echo $this->Html->link(
                            $category->post_count,
                            ['controller' => 'Posts', 'action' => 'index', '?' => ['category' => $category->id]],
                            ['title' => I18N_BELONG_ELEMENT]
                        );
                    } else {
                        echo $category->post_count;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>