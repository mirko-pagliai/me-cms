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
$this->extend('/Admin/Common/view');
$this->assign('title', __d('me_cms', 'Log {0}', $filename));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Download'),
    ['action' => 'download', $filename],
    ['class' => 'btn-success', 'icon' => 'download']
));
$this->append('actions', $this->Form->postButton(
    __d('me_cms', 'Delete'),
    ['action' => 'delete', $filename],
    [
        'class' => 'btn-danger',
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
    ]
));

if (!empty($content)) {
    echo $this->Html->pre($content);
}
