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

use MeCms\Model\Entity\Post;

if (empty($months) || $months->count() < 2) {
    return;
}

$this->extend('/Common/widget');
$this->assign('title', __d('me_cms', 'Posts by month'));

echo $this->Form->create(false, [
    'type' => 'get',
    'url' => ['_name' => 'postsByDate', sprintf('%s/%s', date('Y'), date('m'))],
]);
echo $this->Form->control('q', [
    'id' => false,
    'label' => false,
    'onchange' => 'send_form(this)',
    'options' => $months->map(function (Post $post) {
        return sprintf('%s (%s)', $post->get('month')->i18nFormat('MMMM yyyy'), $post->get('post_count'));
    })->toArray(),
]);
echo $this->Form->end();
