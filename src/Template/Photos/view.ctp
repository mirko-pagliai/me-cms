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
$this->assign('title', $title = $photo->filename);

/**
 * Userbar
 */
if (!$photo->active) {
    $this->userbar($this->Html->span(__d('me_cms', 'Not published'), ['class' => 'label label-warning']));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit photo'),
    ['action' => 'edit', $photo->id, 'prefix' => ADMIN_PREFIX],
    ['icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete photo'),
    ['action' => 'delete', $photo->id, 'prefix' => ADMIN_PREFIX],
    [
        'icon' => 'trash-o',
        'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
        'target' => '_blank',
    ]
));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(__d('me_cms', 'Photos'), ['_name' => 'albums']);
$this->Breadcrumbs->add($photo->album->title, ['_name' => 'album', $photo->album->slug]);
$this->Breadcrumbs->add($title, ['_name' => 'photo', 'slug' => $photo->album->slug, 'id' => $photo->id]);

/**
 * Meta tags
 */
if ($this->request->isAction('view', 'Photos')) {
    $this->Html->meta(['content' => $photo->modified->toUnixString(), 'property' => 'og:updated_time']);

    if ($photo->preview) {
        $this->Html->meta(['href' => $photo->preview['preview'], 'rel' => 'image_src']);
        $this->Html->meta(['content' => $photo->preview['preview'], 'property' => 'og:image']);
        $this->Html->meta(['content' => $photo->preview['width'], 'property' => 'og:image:width']);
        $this->Html->meta(['content' => $photo->preview['height'], 'property' => 'og:image:height']);
    }

    if ($photo->description) {
        $this->Html->meta([
            'content' => $this->Text->truncate(
                trim(strip_tags($this->BBCode->remove($photo->description))),
                100,
                ['html' => true]
            ),
            'property' => 'og:description',
        ]);
    }
}

echo $this->Html->img($photo->thumbnail);
