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
$this->extend('/Posts/index');
$this->assign('title', $title = __d('me_cms', 'Tag {0}', $tag->tag));

/**
 * Userbar
 */
$this->userbar($this->Html->link(__d('me_cms', 'Edit tag'), [
    'controller' => 'PostsTags',
    'action' => 'edit',
    'prefix' => ADMIN_PREFIX,
    $tag->id,
], ['icon' => 'pencil', 'target' => '_blank']));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(__d('me_cms', 'Tags'), ['_name' => 'postsTags']);
$this->Breadcrumbs->add($title, ['_name' => 'postsTag', $tag->slug]);
