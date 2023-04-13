<?php /** @noinspection PhpUnhandledExceptionInspection */
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
 * @var int $assetsSize
 * @var int $cacheSize
 * @var bool $cacheStatus
 * @var int $logsSize
 * @var int $sitemapSize
 * @var \MeCms\View\View\Admin\AppView $this
 * @var int $thumbsSize
 * @var int $totalSize
 */

$this->extend('MeCms./Admin/common/index');
$this->assign('title', __d('me_cms', 'Temporary files'));
?>

<div class="mb-4">
    <h4><?= __d('me_cms', 'All temporary files') ?></h4>
    <p><?= __d('me_cms', 'All temporary files size: {0}', $this->Number->toReadableSize($totalSize)) ?></p>

    <?php if ($this->Identity->isGroup('admin')) : ?>
        <p><?= __d('me_cms', 'This command clear all temporary files: cache, assets, logs and thumbnails') ?></p>

        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all temporary files'),
            ['action' => 'tmpCleaner', 'all'],
            ['class' => 'btn-success', 'icon' => 'trash-alt']
        ) ?>
    <?php endif; ?>
</div>

<hr />

<div class="mb-4">
    <h4><?= __d('me_cms', 'Cache') ?></h4>

    <?php if (!$cacheStatus) : ?>
        <p class="text-danger"><?= __d('me_cms', 'Cache is disabled or debugging is active') ?></p>
    <?php endif; ?>

    <p><?= __d('me_cms', 'Cache size: {0}', $this->Number->toReadableSize($cacheSize)) ?></p>
    <p>
        <?= __d('me_cms', 'Note: you should not need to clear the cache, unless you have not ' .
        'edited the configuration or after an upgrade') ?>
    </p>

    <?= $this->Form->postButton(
        __d('me_cms', 'Clear cache'),
        ['action' => 'tmpCleaner', 'cache'],
        ['class' => 'btn-success', 'icon' => 'trash-alt']
    ) ?>
</div>

<div class="mb-4">
    <h4><?= __d('me_cms', 'Assets') ?></h4>
    <p><?= __d('me_cms', 'Assets size: {0}', $this->Number->toReadableSize($assetsSize)) ?></p>

    <?php if ($assetsSize) : ?>
        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all assets'),
            ['action' => 'tmpCleaner', 'assets'],
            ['class' => 'btn-success', 'icon' => 'trash-alt']
        ) ?>
    <?php endif; ?>
</div>

<div class="mb-4">
    <h4><?= __d('me_cms', 'Logs') ?></h4>
    <p><?= __d('me_cms', 'Logs size: {0}', $this->Number->toReadableSize($logsSize)) ?></p>

    <?php if ($this->Identity->isGroup('admin') && $logsSize) : ?>
        <?= $this->Form->postButton(
            __d('me_cms', 'Clear all logs'),
            ['action' => 'tmpCleaner', 'logs'],
            ['class' => 'btn-success', 'icon' => 'trash-alt']
        ) ?>
    <?php endif; ?>
</div>

<div class="mb-4">
    <h4><?= __d('me_cms', 'Sitemap') ?></h4>
    <p><?= __d('me_cms', 'Sitemap size: {0}', $this->Number->toReadableSize($sitemapSize)) ?></p>

    <?php if ($this->Identity->isGroup('admin') && $sitemapSize) : ?>
        <p><?= __d('me_cms', 'Note: you should not need to clear the sitemap, unless you have recently changed many records') ?></p>

        <?= $this->Form->postButton(
            __d('me_cms', 'Clear sitemap'),
            ['action' => 'tmpCleaner', 'sitemap'],
            ['class' => 'btn-success', 'icon' => 'trash-alt']
        ) ?>
    <?php endif; ?>
</div>

<div class="mb-4">
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
            ['class' => 'btn-success', 'icon' => 'trash-alt']
        ) ?>
    <?php endif; ?>
</div>
