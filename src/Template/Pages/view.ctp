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
$this->extend('/Common/view');
$this->assign('title', $page->title);

/**
 * Userbar
 */
if (!$page->active) {
    $this->userbar($this->Html->span(__d('me_cms', 'Draft'), ['class' => 'label label-warning']));
}
if ($page->created->isFuture()) {
    $this->userbar($this->Html->span(__d('me_cms', 'Scheduled'), ['class' => 'label label-warning']));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit page'),
    ['action' => 'edit', $page->id, 'prefix' => ADMIN_PREFIX],
    ['icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete page'),
    ['action' => 'delete', $page->id, 'prefix' => ADMIN_PREFIX],
    [
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
        'target' => '_blank',
    ]
));

/**
 * Breadcrumb
 */
if (config('page.category')) {
    $this->Breadcrumbs->add($page->category->title, ['_name' => 'pagesCategory', $page->category->slug]);
}
$this->Breadcrumbs->add($page->title, ['_name' => 'page', $page->slug]);

/**
 * Meta tags
 */
if ($this->request->isAction('view', 'Pages')) {
    $this->Html->meta(['content' => 'article', 'property' => 'og:type']);
    $this->Html->meta(['content' => $page->modified->toUnixString(), 'property' => 'og:updated_time']);

    if ($page->preview) {
        $this->Html->meta(['href' => $page->preview['preview'], 'rel' => 'image_src']);
        $this->Html->meta(['content' => $page->preview['preview'], 'property' => 'og:image']);
        $this->Html->meta(['content' => $page->preview['width'], 'property' => 'og:image:width']);
        $this->Html->meta(['content' => $page->preview['height'], 'property' => 'og:image:height']);
    }

    if ($page->text) {
        $this->Html->meta([
            'content' => $this->Text->truncate(
                trim(strip_tags($this->BBCode->remove($page->text))),
                100,
                ['html' => true]
            ),
            'property' => 'og:description',
        ]);
    }
}

echo $this->element('views/page', compact('page'));
