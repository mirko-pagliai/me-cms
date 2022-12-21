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
 * @var \Cake\ORM\ResultSet<\MeCms\Model\Entity\PostsTag> $tags
 * @var \MeCms\View\View\AppView $this
 */

if (empty($tags) || $tags->isEmpty()) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __d('me_cms', 'Popular tags'));

foreach ($tags as $tag) {
    $text = empty($prefix) ? $tag->get('tag') : $prefix . $tag->get('tag');
    $options = ['title' => $tag->get('tag')];
    if ($tag->hasValue('size')) {
        $options['style'] = sprintf('font-size:%spx;', $tag->get('size'));
    }

    echo $this->Html->div(null, $this->Html->link($text, ['_name' => 'postsTag', $tag->get('slug')], $options));
}
