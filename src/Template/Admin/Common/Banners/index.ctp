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
$this->assign('title', __d('me_cms', 'Banners'));

$this->append('actions', $this->Html->button(
    I18N_UPLOAD,
    ['action' => 'upload'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add position'),
    ['controller' => 'BannersPositions', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));

$this->Library->datepicker('#created', ['format' => 'MM-YYYY', 'viewMode' => 'years']);
?>

<?= $this->Form->createInline(null, ['class' => 'filter-form', 'type' => 'get']) ?>
    <fieldset>
        <?= $this->Html->legend(I18N_FILTER, ['icon' => 'eye']) ?>
        <?php
            echo $this->Form->control('id', [
                'default' => $this->request->getQuery('id'),
                'placeholder' => I18N_ID,
                'size' => 1,
            ]);
            echo $this->Form->control('filename', [
                'default' => $this->request->getQuery('filename'),
                'placeholder' => lcfirst(I18N_FILENAME),
                'size' => 13,
            ]);
            echo $this->Form->control('active', [
                'default' => $this->request->getQuery('active'),
                'empty' => I18N_ALL_STATUS,
                'options' => [I18N_YES => I18N_ONLY_PUBLISHED, I18N_NO => I18N_ONLY_NOT_PUBLISHED],
            ]);
            echo $this->Form->control('position', [
                'default' => $this->request->getQuery('position'),
                'empty' => sprintf('-- %s --', __d('me_cms', 'all positions')),
            ]);
            echo $this->Form->datepicker('created', [
                'data-date-format' => 'YYYY-MM',
                'default' => $this->request->getQuery('created'),
                'placeholder' => __d('me_cms', 'month'),
                'size' => 3,
            ]);
            echo $this->Form->submit(null, ['icon' => 'search']);
        ?>
    </fieldset>
<?= $this->Form->end() ?>

<?= $this->element('admin/list-grid-buttons') ?>
<?= $this->fetch('content') ?>

<?= $this->element('MeTools.paginator') ?>