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

use MeCms\Model\Entity\Tag;

if (empty($tags) || $tags->isEmpty()) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __d('me_cms', 'Popular tags'));

echo $this->Form->create(null, [
    'type' => 'get',
    'url' => ['_name' => 'postsTag', 'tag'],
]);
echo $this->Form->control('q', [
    'id' => false,
    'label' => false,
    'onchange' => 'sendForm(this)',
    'options' => $tags->map(fn(Tag $tag): string => sprintf('%s (%d)', $tag->get('tag'), $tag->get('post_count')))->toArray(),
]);
echo $this->Form->end();
