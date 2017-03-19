<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */

$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Banners'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Upload'),
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
        <?= $this->Html->legend(__d('me_cms', 'Filter'), ['icon' => 'eye']) ?>
        <?php
            echo $this->Form->control('id', [
                'default' => $this->request->getQuery('id'),
                'placeholder' => __d('me_cms', 'ID'),
                'size' => 2,
            ]);
            echo $this->Form->control('filename', [
                'default' => $this->request->getQuery('filename'),
                'placeholder' => __d('me_cms', 'filename'),
                'size' => 16,
            ]);
            echo $this->Form->control('active', [
                'default' => $this->request->getQuery('active'),
                'empty' => sprintf('-- %s --', __d('me_cms', 'all status')),
                'options' => [
                    'yes' => __d('me_cms', 'Only published'),
                    'no' => __d('me_cms', 'Only not published'),
                ],
            ]);
            echo $this->Form->control('position', [
                'default' => $this->request->getQuery('position'),
                'empty' => sprintf('-- %s --', __d('me_cms', 'all positions')),
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

<?= $this->element('admin/list-grid-buttons') ?>

<?= $this->fetch('content') ?>

<?= $this->element('MeTools.paginator') ?>