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
$this->assign('title', I18N_TAGS);
$this->append('actions', $this->Html->button(
    I18N_ADD,
    ['action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
?>

<?= $this->Html->cssStart() ?>
    .index > div > div {
        margin-bottom: -10px;
        padding: 10px 5px;
    }

    .index > div > div > div {
        background-color: #f9f9f9;
        border-bottom: 1px solid #ddd;
        padding: 15px 15px;
    }
<?= $this->Html->cssEnd() ?>

<?= $this->Form->createInline(false, ['class' => 'filter-form', 'type' => 'get']) ?>
    <fieldset>
        <?= $this->Html->legend(I18N_FILTER, ['icon' => 'eye']) ?>
        <?php
            echo $this->Form->control('id', [
                'default' => $this->request->getQuery('id'),
                'placeholder' => I18N_ID,
                'size' => 2,
            ]);
            echo $this->Form->control('name', [
                'default' => $this->request->getQuery('name'),
                'placeholder' => I18N_NAME,
                'size' => 16,
            ]);
            echo $this->Form->submit(null, ['icon' => 'search']);
        ?>
    </fieldset>
<?= $this->Form->end() ?>

<div class="div-striped row">
    <?php foreach ($tags as $tag) : ?>
        <div class="col-sm-3">
            <div>
                <div class="small">
                    <?= I18N_ID ?> <code><?= $tag->id ?></code>
                </div>
                <div class="no-wrap">
                    <?= $this->Html->link(
                        $this->Html->strong($tag->tag),
                        ['controller' => 'PostsTags', 'action' => 'edit', $tag->id]
                    ) ?>
                </div>
                <div class="small">
                    <?= sprintf('(%s)', $this->Html->link(
                        __dn('me_cms', '{0} post', '{0} posts', $tag->post_count, $tag->post_count),
                        ['controller' => 'Posts', 'action' => 'index', '?' => ['tag' => $tag->tag]],
                        ['title' => I18N_BELONG_ELEMENT]
                    )) ?>
                    </div>
                <?php
                $actions = [];

                //Only admins and managers can edit tags
                if ($this->Auth->isGroup(['admin', 'manager'])) {
                    $actions[] = $this->Html->link(
                        I18N_EDIT,
                        ['controller' => 'PostsTags', 'action' => 'edit', $tag->id],
                        ['icon' => 'pencil']
                    );
                }

                $actions[] = $this->Html->link(
                    I18N_OPEN,
                    ['_name' => 'postsTag', $tag->slug],
                    ['icon' => 'external-link', 'target' => '_blank']
                );

                echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->element('MeTools.paginator') ?>