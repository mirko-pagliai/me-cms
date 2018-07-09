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
    I18N_DOWNLOAD,
    ['action' => 'download', $filename],
    ['class' => 'btn-success', 'icon' => 'download']
));
$this->append('actions', $this->Form->postButton(
    I18N_DELETE,
    ['action' => 'delete', $filename],
    ['class' => 'btn-danger', 'icon' => 'trash-alt', 'confirm' => I18N_SURE_TO_DELETE]
));

if (!empty($content)) {
    echo $this->Html->pre($content);
}
