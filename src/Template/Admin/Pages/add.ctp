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
$this->assign('title', $title = __d('me_cms', 'Add page'));
$this->Library->ckeditor();
$this->Library->datetimepicker();
$this->Library->slugify();
?>

<?= $this->Form->create($page); ?>
<div class="row">
    <div class="col-lg-3 order-12">
        <div class="float-form">
        <?php
            echo $this->Form->control('category_id', [
                'default' => $categories->count() < 2 ? $categories->first() : false,
                'label' => I18N_CATEGORY,
            ]);
            echo $this->Form->datetimepicker('created', [
                'help' => [I18N_USE_CURRENT_DATETIME, I18N_DELAY_PUBLICATION],
                'label' => I18N_DATE,
            ]);
            echo $this->Form->control('priority', [
                'default' => '3',
                'label' => I18N_PRIORITY,
            ]);
            echo $this->Form->control('active', [
                'checked' => true,
                'help' => I18N_HELP_DRAFT,
                'label' => I18N_PUBLISHED,
            ]);
        ?>
        </div>
    </div>
    <fieldset class="col-lg-9">
    <?php
        echo $this->Form->control('title', [
            'id' => 'title',
            'label' => I18N_TITLE,
        ]);
        echo $this->Form->control('subtitle', [
            'label' => I18N_SUBTITLE,
        ]);
        echo $this->Form->control('slug', [
            'id' => 'slug',
            'help' => I18N_HELP_SLUG,
            'label' => I18N_SLUG,
        ]);
        echo $this->Form->ckeditor('text', [
            'label' => I18N_TEXT,
            'rows' => 10,
        ]);
    ?>
    <?= $this->element('admin/bbcode') ?>
    </fieldset>
</div>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>