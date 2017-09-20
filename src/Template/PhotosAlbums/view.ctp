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
$this->assign('title', $title = $album->title);

if (getConfig('default.fancybox')) {
    $this->Library->fancybox();
}

/**
 * Userbar
 */
if (!$album->active) {
    $this->userbar($this->Html->span(I18N_NOT_PUBLISHED, ['class' => 'badge badge-warning']));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit album'),
    ['action' => 'edit', $album->id, 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link', 'icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete album'),
    ['action' => 'delete', $album->id, 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link text-danger', 'icon' => 'trash-o', 'confirm' => I18N_SURE_TO_DELETE, 'target' => '_blank']
));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(I18N_PHOTOS, ['_name' => 'albums']);
$this->Breadcrumbs->add($title, ['_name' => 'album', $album->slug]);

//Sets base options for each photo
$baseOptions = ['class' => 'd-block'];

//If Fancybox is enabled
if (getConfig('default.fancybox')) {
    $baseOptions = ['class' => 'd-block fancybox', 'rel' => 'fancybox-group'];
}
?>

<div class="row">
    <?php
    foreach ($photos as $photo) {
        $link = ['_name' => 'photo', 'slug' => $album->slug, 'id' => $photo->id];
        $linkOptions = $baseOptions + ['title' => $photo->description];
        $path = $photo->path;
        $text = $photo->description;

        //If Fancybox is enabled, adds some options
        if (getConfig('default.fancybox')) {
            $linkOptions += ['data-fancybox-href' => $this->Thumb->resizeUrl($photo->path, ['height' => 1280])];
        }

        echo $this->Html->div(
            'col-md-4 col-lg-3 mb-4',
            $this->element(ME_CMS . '.views/photo-preview', compact('link', 'linkOptions', 'path', 'text'))
        );
    }
    ?>
</div>

<?= $this->element('MeTools.paginator') ?>