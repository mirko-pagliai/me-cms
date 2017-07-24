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
if (empty($pages) || $pages->isEmpty()) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Pages'));

$pages = $pages->map(function ($page) {
    return $this->Html->link($page->title, ['_name' => 'page', $page->slug]);
})->toArray();

echo $this->Html->ul($pages, ['icon' => 'caret-right']);
