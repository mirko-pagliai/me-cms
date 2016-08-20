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

$this->extend('/Common/index');
$this->assign('title', $category->title);

/**
 * Userbar
 */
$this->userbar([
    $this->Html->link(
        __d('me_cms', 'Edit category'),
        ['action' => 'edit', $category->id, 'prefix' => 'admin'],
        ['icon' => 'pencil', 'target' => '_blank']
    ),
    $this->Form->postLink(
        __d('me_cms', 'Delete category'),
        ['action' => 'delete', $category->id, 'prefix' => 'admin'],
        [
            'class' => 'text-danger',
            'icon' => 'trash-o',
            'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
            'target' => '_blank',
        ]
    ),
]);

/**
 * Breadcrumb
 */
$this->Breadcrumb->add(
    $category->title,
    ['_name' => 'pages_category', $category->title]
);

$pages = array_map(function ($page) {
    return $this->Html->link($page->title, ['_name' => 'page', $page->slug]);
}, $category->pages);

echo $this->Html->ul($pages, ['icon' => 'caret-right']);
