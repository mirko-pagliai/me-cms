<?php
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
 * @var \MeCms\Model\Entity\PostsTag $tag
 * @var \MeCms\View\View\Admin\AppView $this
 */
$this->extend('MeCms./common/form');
$this->assign('title', $title = __d('me_cms', 'Edit tag'));
?>

<?= $this->Form->create($tag); ?>
<fieldset>
    <?= $this->Form->control('tag', ['label' => __d('me_cms', 'Tag')]); ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>
