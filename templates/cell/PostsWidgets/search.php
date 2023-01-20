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
 * @var \MeCms\View\View\AppView $this
 */

if ($this->getRequest()->is('url', ['_name' => 'postsSearch'])) {
    return;
}

$this->extend('MeCms./common/widget');
$this->assign('title', __d('me_cms', 'Search posts'));

echo $this->Form->createInline(null, [
    'type' => 'get',
    'url' => ['_name' => 'postsSearch'],
]);
echo $this->Form->control('p', [
    'append-text' => $this->Form->submit(null, ['class' => 'btn-primary', 'icon' => 'search']),
    'id' => false,
    'label' => false,
    'placeholder' => sprintf('%s...', __d('me_cms', 'Search')),
]);
echo $this->Form->end();
