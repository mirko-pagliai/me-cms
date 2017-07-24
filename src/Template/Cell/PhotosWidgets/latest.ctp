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
if (empty($photos) || $photos->isEmpty()) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __dn('me_cms', 'Latest photo', 'Latest {0} photos', $photos->count(), $photos->count()));

foreach ($photos as $photo) {
    echo $this->Html->link(
        $this->Thumb->fit($photo->path, ['width' => 253]),
        ['_name' => 'albums'],
        ['class' => 'thumbnail']
    );
}
