<?php
/**
 * This file is part of MeCms.
 *
 * MeCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */

$this->extend('/Admin/Common/view');
$this->assign('title', __d('me_cms', 'Log {0}', $log->filename));

$this->append('actions', $this->Html->button(
    __d('me_cms', 'Download'),
    ['action' => 'download', $log->filename],
    ['class' => 'btn-success', 'icon' => 'download']
));
$this->append('actions', $this->Form->postButton(
    __d('me_cms', 'Delete'),
    ['action' => 'delete', $log->filename],
    [
        'class' => 'btn-danger',
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
    ]
));

if (!empty($log->content)) {
    echo $this->Html->pre($log->content);
}
