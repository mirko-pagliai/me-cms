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

use MeCms\Model\Entity\Post;

if (empty($posts) || $posts->count() < 2) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Posts by month'));

$months = $posts->map(function (Post $post) {
    return $this->Html->link($post->get('month')->i18nFormat('MMMM yyyy'), [
        '_name' => 'postsByDate',
        sprintf('%s/%s', $post->get('month')->i18nFormat('yyyy'), $post->get('month')->i18nFormat('MM')),
    ]);
})->toArray();

echo $this->Html->ul($months, ['icon' => 'caret-right']);
