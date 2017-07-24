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
$this->assign('title', $title = __d('me_cms', 'Edit posts category'));
$this->Library->slugify();
?>

<?= $this->Form->create($category); ?>
<div class='float-form'>
    <?php
    if (!empty($categories)) {
        echo $this->Form->control('parent_id', [
            'label' => __d('me_cms', 'Parent category'),
            'options' => $categories,
            'help' => __d('me_cms', 'Leave blank to create a parent category'),
        ]);
    }
    ?>
</div>
<fieldset>
    <?php
        echo $this->Form->control('title', [
            'id' => 'title',
            'label' => __d('me_cms', 'Title'),
        ]);
        echo $this->Form->control('slug', [
            'id' => 'slug',
            'label' => __d('me_cms', 'Slug'),
            'help' => __d('me_cms', 'The slug is a string identifying a resource. If ' .
                'you do not have special needs, let it be generated automatically'),
        ]);
        echo $this->Form->control('description', [
            'label' => __d('me_cms', 'Description'),
            'rows' => 3,
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>