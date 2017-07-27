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
if (empty($tags) || $tags->isEmpty()) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Popular tags'));

foreach ($tags as $tag) {
    $text = empty($prefix) ? $tag->tag : $prefix . $tag->tag;

    $options = ['title' => $tag->tag];

    if (!empty($tag->size)) {
        $options['style'] = sprintf('font-size:%spx;', $tag->size);
    }

    echo $this->Html->div(null, $this->Html->link($text, ['_name' => 'postsTag', $tag->slug], $options));
}
