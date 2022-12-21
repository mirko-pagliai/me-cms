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
 * @var \Cake\ORM\ResultSet<array{created: \Cake\I18n\FrozenTime, post_count: int}> $months
 * @var \MeCms\View\View\AppView $this
 */

if (empty($months) || count($months) < 2) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __d('me_cms', 'Posts by month'));

echo $this->Form->create(null, [
    'type' => 'get',
    'url' => ['_name' => 'postsByDate', sprintf('%s/%s', date('Y'), date('m'))],
]);
echo $this->Form->control('q', [
    'id' => false,
    'label' => false,
    'onchange' => 'sendForm(this)',
    'options' => $months->map(fn(array $month): string => sprintf('%s (%d)', $month['created']->i18nFormat('MMMM yyyy'), $month['post_count'])),
]);
echo $this->Form->end();
