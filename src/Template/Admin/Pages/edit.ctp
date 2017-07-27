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
            'help' => __d('me_cms', 'The slug is a string identifying a resource. If ' .
                'you do not have special needs, let it be generated automatically'),
        ]);
        echo $this->Form->ckeditor('text', [
            'label' => __d('me_cms', 'Text'),
        ]);
    ?>
    <?= $this->element('admin/bbcode') ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>