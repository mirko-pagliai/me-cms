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

$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Edit page'));

$this->Library->ckeditor();
$this->Library->datetimepicker();
$this->Library->slugify();
?>

<?= $this->Form->create($page); ?>
<div class='float-form'>
    <?php
        echo $this->Form->control('category_id', [
            'empty' => false,
            'label' => __d('me_cms', 'Category'),
        ]);
        echo $this->Form->datetimepicker('created', [
            'label' => __d('me_cms', 'Date'),
            'help' => [
                __d('me_cms', 'If blank, the current date and time will be used'),
                __d('me_cms', 'You can delay the publication by entering a future date'),
            ],
            'value' => $page->created->i18nFormat(FORMAT_FOR_MYSQL),
        ]);
        echo $this->Form->control('priority', [
            'label' => __d('me_cms', 'Priority'),
        ]);
        echo $this->Form->control('active', [
            'label' => sprintf('%s?', __d('me_cms', 'Published')),
            'help' => __d('me_cms', 'Disable this option to save as a draft'),
        ]);
    ?>
</div>
<fieldset>
    <?php
        echo $this->Form->control('title', [
            'id' => 'title',
            'label' => __d('me_cms', 'Title'),
        ]);
        echo $this->Form->control('subtitle', [
            'label' => __d('me_cms', 'Subtitle'),
        ]);
        echo $this->Form->control('slug', [
            'id' => 'slug',
            'label' => __d('me_cms', 'Slug'),
            'help' => __d('me_cms', 'The slug is a string identifying a ' .
                'resource. If you do not have special needs, let it be ' .
                'generated automatically'),
        ]);
        echo $this->Form->ckeditor('text', [
            'label' => __d('me_cms', 'Text'),
        ]);
    ?>
    <?= $this->element('admin/bbcode') ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>