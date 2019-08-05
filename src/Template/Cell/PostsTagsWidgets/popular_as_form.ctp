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

use MeCms\Model\Entity\Tag;

if (empty($tags) || $tags->isEmpty()) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Popular tags'));

echo $this->Form->create(false, [
    'type' => 'get',
    'url' => ['_name' => 'postsTag', 'tag'],
]);
echo $this->Form->control('q', [
    'id' => false,
    'label' => false,
    'onchange' => 'send_form(this)',
    'options' => $tags->map(function (Tag $tag) {
        return sprintf('%s (%d)', $tag->get('tag'), $tag->get('post_count'));
    })->toArray(),
]);
echo $this->Form->end();
