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
if (empty($photos) || $photos->isEmpty()) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __dn('me_cms', 'Random photo', 'Random {0} photos', $photos->count(), $photos->count()));

foreach ($photos as $photo) {
    echo $this->Thumb->fit($photo->get('path'), ['width' => 253], ['class' => 'thumbnail', 'url' => ['_name' => 'albums']]);
}
