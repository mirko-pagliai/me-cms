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
 */
if (empty($albums) || $albums->count() < 2) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Albums'));

$albums = $albums->map(function ($album) {
    return $this->Html->link($album->title, ['_name' => 'album', $album->slug]);
})->toArray();

echo $this->Html->ul($albums, ['icon' => 'caret-right']);
