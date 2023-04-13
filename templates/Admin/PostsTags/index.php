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
 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\PostsTag> $tags
 * @var \MeCms\View\View\Admin\AppView $this
 */

$this->extend('MeCms./Admin/common/index');
$this->assign('title', I18N_TAGS);
$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));

echo $this->Form->createInline(null, ['class' => 'filter-form', 'type' => 'get']);
echo $this->Html->legend(I18N_FILTER, ['icon' => 'eye']);
echo $this->Form->control('id', [
    'default' => $this->getRequest()->getQuery('id'),
    'placeholder' => I18N_ID,
    'size' => 1,
]);
echo $this->Form->control('name', [
    'default' => $this->getRequest()->getQuery('name'),
    'placeholder' => I18N_NAME,
    'size' => 13,
]);
echo $this->Form->submit(null, ['icon' => 'search']);
echo $this->Form->end();
?>

<div class="row">
    <?php foreach ($tags as $tag) : ?>
        <div class="col-md-3 mb-4">
            <div class="card bg-light px-3 py-2 border-0">
                <div>
                    <code><?= I18N_ID ?> <?= $tag->get('id') ?></code>
                </div>
                <div class="mb-1">
                    <?= $this->Html->link($tag->get('tag'), ['controller' => 'PostsTags', 'action' => 'edit', $tag->get('id')], ['class' => 'fw-bold']) ?>
                </div>
                <div class="mb-1">
                    <?= sprintf('(%s)', $this->Html->link(
                        __dn('me_cms', '{0} post', '{0} posts', $tag->get('post_count'), $tag->get('post_count')),
                        ['controller' => 'Posts', 'action' => 'index', '?' => ['tag' => $tag->get('tag')]],
                        ['title' => I18N_BELONG_ELEMENT]
                    )) ?>
                </div>
                <?php
                $actions = [];

                //Only admins and managers can edit
                if ($this->Identity->isGroup('admin', 'manager')) {
                    $actions[] = $this->Html->link(
                        I18N_EDIT,
                        ['controller' => 'PostsTags', 'action' => 'edit', $tag->get('id')],
                        ['icon' => 'pencil-alt']
                    );
                }

                $actions[] = $this->Html->link(
                    I18N_OPEN,
                    ['_name' => 'postsTag', $tag->get('slug')],
                    ['icon' => 'external-link-alt', 'target' => '_blank']
                );

                echo $this->Html->ul($actions, ['class' => 'actions mt-0 p-0']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->element('MeTools.paginator') ?>
