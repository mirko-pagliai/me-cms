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

echo $this->Thumb->resize($photo->path, ['width' => 1200]);
