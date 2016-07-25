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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
    $this->extend('/Admin/Common/index');
    $this->assign('title', __d('me_cms', 'Temporary files'));
?>

<div class="margin-20">
    <?= $this->Html->h4(__d('me_cms', 'All temporary files')) ?>
    <p><?= __d('me_cms', 'All temporary files size: {0}', $this->Number->toReadableSize($total_size)) ?></p>

    <?php if($this->Auth->isGroup('admin')): //Only admins can clear all temporary files ?>
        <p><?= __d('me_cms', 'This command clear all temporary files: cache, assets, logs and thumbnails') ?></p>
        <?= $this->Form->postButton(__d('me_cms', 'Clear all temporary files'), ['action' => 'tmp_cleaner', 'all'], ['class' => 'btn-success', 'icon' => 'trash-o']) ?>
    <?php endif; ?>
</div>

<hr />

<div class="margin-20">
    <?= $this->Html->h4(__d('me_cms', 'Cache')) ?>
    <?php if(!$cache_status): ?>
        <?= $this->Html->para('text-danger', __d('me_cms', 'The cache is disabled or debugging is active')) ?>
    <?php endif; ?>
    <p><?= __d('me_cms', 'Cache size: {0}', $this->Number->toReadableSize($cache_size)) ?></p>
    <p><?= __d('me_cms', 'Note: you should not need to clear the cache, unless you have not edited the configuration or after an upgrade') ?></p>
    <?= $this->Form->postButton(__d('me_cms', 'Clear cache'), ['action' => 'tmp_cleaner', 'cache'], ['class' => 'btn-success', 'icon' => 'trash-o']) ?>
</div>

<div class="margin-20">
    <?= $this->Html->h4(__d('me_cms', 'Assets')) ?>
    <p><?= __d('me_cms', 'Assets size: {0}', $this->Number->toReadableSize($assets_size)) ?></p>
    <?php if($assets_size): ?>
        <?= $this->Form->postButton(__d('me_cms', 'Clear all assets'), ['action' => 'tmp_cleaner', 'assets'], ['class' => 'btn-success', 'icon' => 'trash-o']) ?>
    <?php endif; ?>
</div>

<div class="margin-20">
    <?= $this->Html->h4(__d('me_cms', 'Logs')) ?>
    <p><?= __d('me_cms', 'Logs size: {0}', $this->Number->toReadableSize($logs_size)) ?></p>

    <?php if($this->Auth->isGroup('admin')): //Only admins can clear logs ?>
        <?php if($logs_size): ?>
            <?= $this->Form->postButton(__d('me_cms', 'Clear all logs'), ['action' => 'tmp_cleaner', 'logs'], ['class' => 'btn-success', 'icon' => 'trash-o']) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="margin-20">
    <?= $this->Html->h4(__d('me_cms', 'Sitemap')) ?>
    <p><?= __d('me_cms', 'Sitemap size: {0}', $this->Number->toReadableSize($sitemap_size)) ?></p>

    <?php if($this->Auth->isGroup('admin')): //Only admins can clear sitemap ?>
        <?php if($sitemap_size): ?>
            <p><?= __d('me_cms', 'Note: you should not need to clear the sitemap, unless you have recently changed many records') ?></p>
            <?= $this->Form->postButton(__d('me_cms', 'Clear sitemap'), ['action' => 'tmp_cleaner', 'sitemap'], ['class' => 'btn-success', 'icon' => 'trash-o']) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="margin-20">
    <?= $this->Html->h4(__d('me_cms', 'Thumbnails')) ?>
    <p><?= __d('me_cms', 'Thumbnails size: {0}', $this->Number->toReadableSize($thumbs_size)) ?></p>
    <?php if($thumbs_size): ?>
        <p>
            <?= __d('me_cms', 'Note: you should not need to clear the thumbnails and that this will slow down the images loading the first time that are displayed. '
                . 'You should clear thumbnails only when they have reached a large size or when many images are no longer used') ?>
        </p>
    <?= $this->Form->postButton(__d('me_cms', 'Clear all thumbnails'), ['action' => 'tmp_cleaner', 'thumbs'], ['class' => 'btn-success', 'icon' => 'trash-o']) ?>
    <?php endif; ?>
</div>