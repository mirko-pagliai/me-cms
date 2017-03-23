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
$this->assign('title', $title = __d('me_cms', 'Add post'));

$this->Library->ckeditor();
$this->Library->datetimepicker();
$this->Library->slugify();
$this->Asset->script('MeCms.admin/tags', ['block' => 'script_bottom']);
?>

<?= $this->Form->create($post); ?>
<div class='float-form'>
    <?php
    //Only admins and managers can add posts on behalf of other users
    if ($this->Auth->isGroup(['admin', 'manager'])) {
        echo $this->Form->control('user_id', [
            'default' => $this->Auth->user('id'),
            'label' => __d('me_cms', 'Author'),
        ]);
    }

    echo $this->Form->control('category_id', [
        'default' => count($categories) < 2 ? collection($categories)->first() : false,
        'label' => __d('me_cms', 'Category'),
    ]);
    echo $this->Form->datetimepicker('created', [
        'label' => __d('me_cms', 'Date'),
        'help' => [
            __d('me_cms', 'If blank, the current date and time will be used'),
            __d('me_cms', 'You can delay the publication by entering a future date'),
        ],
    ]);
    echo $this->Form->control('priority', [
        'default' => '3',
        'label' => __d('me_cms', 'Priority'),
    ]);
    echo $this->Form->control('active', [
        'checked' => true,
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
            'help' => __d('me_cms', 'The slug is a string identifying a resource. If ' .
                'you do not have special needs, let it be generated automatically'),
        ]);
    ?>
    <div class="form-group to-be-hidden">
        <?= $this->Form->control('tags_as_string', [
            'id' => 'tags-output-text',
            'label' => __d('me_cms', 'Tags'),
            'rows' => 2,
            'help' => __d('me_cms', 'Tags must be at least 3 chars and ' .
                'separated by a comma or a comma and a space. Only ' .
                'lowercase letters, numbers, hyphen, space'),
        ]) ?>
    </div>
    <div class="form-group hidden to-be-shown">
        <div id="tags-preview">
            <?= sprintf('%s:', __d('me_cms', 'Tags')) ?>
        </div>
        <?php
            echo $this->Form->control('add_tags', [
                'button' => $this->Form->button(null, [
                    'class' => 'btn-success',
                    'icon' => 'plus',
                    'id' => 'tags-input-button',
                ]),
                'id' => 'tags-input-text',
                'label' => false,
                'help' => __d('me_cms', 'Tags must be at least 3 chars and ' .
                    'separated by a comma or a comma and a space. Only ' .
                    'lowercase letters, numbers, hyphen, space'),
            ]);

            //Tags error
            if ($this->Form->isFieldError('tags')) {
                echo $this->Form->error('tags');
            }
        ?>
    </div>
    <?= $this->Form->ckeditor('text', [
        'label' => __d('me_cms', 'Text'),
        'rows' => 10,
    ]) ?>
    <?= $this->element('admin/bbcode') ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>