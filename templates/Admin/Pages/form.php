<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\PagesCategory> $categories
 * @var \MeCms\Model\Entity\Page $page
 * @var \MeCms\View\View\Admin\AppView $this
 * @var string $title
 */

$this->extend('MeCms./common/form');
$this->Library->ckeditor();
$this->Library->slugify();

$defaultCategory = $categories->count() < 2 ? $categories->first() : false;
$emptyCategory = !$defaultCategory && $this->getTemplate() !== 'edit';
?>

<?= $this->Form->create($page); ?>
<div class="row">
    <div class="col-lg-3 order-last">
        <div class="float-form">
        <?php
        echo $this->Form->control('category_id', [
            'default' => $defaultCategory,
            'empty' => $emptyCategory,
            'label' => I18N_CATEGORY,
        ]);
        echo $this->Form->control('created', [
            'help' => [I18N_USE_CURRENT_DATETIME, I18N_DELAY_PUBLICATION],
            'label' => I18N_DATE,
        ]);
        echo $this->Form->control('priority', [
            'label' => I18N_PRIORITY,
        ]);
        echo $this->Form->control('active', [
            'help' => I18N_HELP_DRAFT,
            'label' => I18N_PUBLISHED,
        ]);
        echo $this->Form->control('enable_comments', [
            'default' => false,
            'help' => I18N_HELP_ENABLE_COMMENTS,
            'label' => I18N_ENABLE_COMMENTS,
        ]);
        ?>
        </div>
    </div>
    <fieldset class="col">
    <?php
    echo $this->Form->control('title', [
        'id' => 'title',
        'label' => I18N_TITLE,
    ]);
    echo $this->Form->control('subtitle', [
        'label' => I18N_SUBTITLE,
    ]);
    echo $this->Form->control('slug', [
        'help' => I18N_HELP_SLUG,
        'id' => 'slug',
        'label' => I18N_SLUG,
    ]);
    echo $this->Form->ckeditor('text', [
        'label' => I18N_TEXT,
    ]);
    ?>
    <?= $this->element('admin/bbcode') ?>
    </fieldset>
</div>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>
