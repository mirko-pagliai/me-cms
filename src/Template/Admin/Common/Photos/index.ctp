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
$this->assign('title', __d('me_cms', 'Photos'));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Upload'),
    ['action' => 'upload'],
    ['class' => 'btn-success', 'icon' => 'plus']
));
$this->append('actions', $this->Html->button(
    __d('me_cms', 'Add album'),
    ['controller' => 'PhotosAlbums', 'action' => 'add'],
    ['class' => 'btn-success', 'icon' => 'plus']
));

$this->Library->datepicker('#created', ['format' => 'MM-YYYY', 'viewMode' => 'years']);
?>

<?= $this->Form->createInline(null, ['class' => 'filter-form', 'type' => 'get']) ?>
    <fieldset>
        <?= $this->Html->legend(__d('me_cms', 'Filter'), ['icon' => 'eye']) ?>
        <?php
            echo $this->Form->input('id', [
                'default' => $this->request->query('id'),
                'placeholder' => __d('me_cms', 'ID'),
                'size' => 2,
            ]);
            echo $this->Form->input('filename', [
                'default' => $this->request->query('filename'),
                'placeholder' => __d('me_cms', 'filename'),
                'size' => 16,
            ]);
            echo $this->Form->input('active', [
                'default' => $this->request->query('active'),
                'empty' => sprintf('-- %s --', __d('me_cms', 'all status')),
                'options' => [
                    'yes' => __d('me_cms', 'Only published'),
                    'no' => __d('me_cms', 'Only not published'),
                ],
            ]);
            echo $this->Form->input('album', [
                'default' => $this->request->query('album'),
                'empty' => sprintf('-- %s --', __d('me_cms', 'all albums')),
            ]);
            echo $this->Form->datepicker('created', [
                'data-date-format' => 'YYYY-MM',
                'default' => $this->request->query('created'),
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