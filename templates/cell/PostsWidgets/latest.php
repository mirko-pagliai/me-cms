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

 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\Post> $posts
 * @var \MeCms\View\View\AppView $this
 */

use MeCms\Model\Entity\Post;

if (empty($posts) || $posts->isEmpty()) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __dn('me_cms', 'Latest post', 'Latest {0} posts', $posts->count(), $posts->count()));

$posts = $posts->map(fn(Post $post): string => $this->Html->link($post->get('title'), ['_name' => 'post', $post->get('slug')]))->toArray();

echo $this->Html->ul($posts, ['icon' => 'caret-right']);
