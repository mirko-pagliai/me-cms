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

use MeCms\Model\Entity\PhotosAlbum;

if (empty($albums) || $albums->count() < 2) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Albums'));

$albums = $albums->map(function (PhotosAlbum $album) {
    return $this->Html->link($album->get('title'), ['_name' => 'album', $album->get('slug')]);
})->toArray();

echo $this->Html->ul($albums, ['icon' => 'caret-right']);
