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
 * @var \Feed\View\RssView $this
 */
$channelData = ($channelData ?? []) + ['title' => $this->fetch('title')];

$channel = $this->Rss->channel([], $channelData, $this->fetch('content'));
echo $this->Rss->document($documentData ?? [], $channel);
