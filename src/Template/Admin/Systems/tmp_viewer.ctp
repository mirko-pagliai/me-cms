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
$this->assign('title', __d('me_cms', 'Temporary files'));
?>

<div class="margin-20">
    <h4><?= __d('me_cms', 'All temporary files') ?></h4>
    <p><?= __d('me_cms', 'All temporary files size: {0}', $this->Number->toReadableSize($totalSize)) ?></p>

    <?php if ($this->Auth->isGroup('admin')) : ?>
        <p><?= __d('me_cms', 'This command clear all temporary files: cache, assets, logs and thumbnails') ?></p>

        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all temporary files'),
            ['action' => 'tmpCleaner', 'all'],
            ['class' => 'btn-success', 'icon' => 'trash-o']
        ) ?>
    <?php endif; ?>
</div>

<hr />

<div class="margin-20">
    <h4><?= __d('me_cms', 'Cache') ?></h4>

    <?php if (!$cacheStatus) : ?>
        <p class="text-danger"><?= __d('me_cms', 'The cache is disabled or debugging is active') ?></p>
    <?php endif; ?>

    <p><?= __d('me_cms', 'Cache size: {0}', $this->Number->toReadableSize($cacheSize)) ?></p>
    <p>
        <?= __d('me_cms', 'Note: you should not need to clear the cache, unless you have not ' .
        'edited the configuration or after an upgrade') ?>
    </p>

    <?= $this->Form->postButton(
        __d('me_cms', 'Clear cache'),
        ['action' => 'tmpCleaner', 'cache'],
        ['class' => 'btn-success', 'icon' => 'trash-o']
    ) ?>
</div>

<div class="margin-20">
    <h4><?= __d('me_cms', 'Assets') ?></h4>
    <p><?= __d('me_cms', 'Assets size: {0}', $this->Number->toReadableSize($assetsSize)) ?></p>

    <?php if ($assetsSize) : ?>
        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all assets'),
            ['action' => 'tmpCleaner', 'assets'],
            ['class' => 'btn-success', 'icon' => 'trash-o']
        ) ?>
    <?php endif; ?>
</div>

<div class="margin-20">
    <h4><?= __d('me_cms', 'Logs') ?></h4>
    <p><?= __d('me_cms', 'Logs size: {0}', $this->Number->toReadableSize($logsSize)) ?></p>

    <?php if ($this->Auth->isGroup('admin') && $logsSize) : ?>
        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all logs'),
            ['action' => 'tmpCleaner', 'logs'],
            ['class' => 'btn-success', 'icon' => 'trash-o']
        ) ?>
    <?php endif; ?>
</div>

<div class="margin-20">
    <h4><?= __d('me_cms', 'Sitemap') ?></h4>
    <p><?= __d('me_cms', 'Sitemap size: {0}', $this->Number->toReadableSize($sitemapSize)) ?></p>

    <?php if ($this->Auth->isGroup('admin') && $sitemapSize) : ?>
        <p><?= __d('me_cms', 'Note: you should not need to clear the sitemap, unless you have recently changed many records') ?></p>

        <?= $this->Form->postButton(
            __d('me_cms', 'Clear sitemap'),
            ['action' => 'tmpCleaner', 'sitemap'],
            ['class' => 'btn-success', 'icon' => 'trash-o']
        ) ?>
    <?php endif; ?>
    ?>
</div>

<div class="margin-20">
    <h4><?= __d('me_cms', 'Thumbnails') ?></h4>
    <p><?= __d('me_cms', 'Thumbnails size: {0}', $this->Number->toReadableSize($thumbsSize)) ?></p>

    <?php if ($thumbsSize) : ?>
        <p>
            <?= __d('me_cms', 'Note: you should not need to clear the thumbnails and that this will slow down the ' .
                'images loading the first time that are displayed. You should clear thumbnails only ' .
                'when they have reached a large size or when many images are no longer used') ?>
        </p>

        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all thumbnails'),
            ['action' => 'tmpCleaner', 'thumbs'],
            ['class' => 'btn-success', 'icon' => 'trash-o']
        ) ?>
    <?php endif; ?>
</div>