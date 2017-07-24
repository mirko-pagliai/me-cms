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
if (empty($posts) || $posts->isEmpty()) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __dn('me_cms', 'Latest post', 'Latest {0} posts', $posts->count(), $posts->count()));

$posts = $posts->map(function ($post) {
    return $this->Html->link($post->title, ['_name' => 'post', $post->slug]);
})->toArray();

echo $this->Html->ul($posts, ['icon' => 'caret-right']);
