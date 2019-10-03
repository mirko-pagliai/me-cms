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
$this->assign('title', $album->get('title'));

if (getConfig('default.fancybox')) {
    $this->Library->fancybox();
}

/**
 * Userbar
 */
if (!$album->get('active')) {
    $this->userbar($this->Html->span(I18N_NOT_PUBLISHED, ['class' => 'badge badge-warning']));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit album'),
    ['action' => 'edit', $album->get('id'), 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link', 'icon' => 'pencil-alt', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete album'),
    ['action' => 'delete', $album->get('id'), 'prefix' => ADMIN_PREFIX],
    ['class' => 'nav-link text-danger', 'icon' => 'trash-alt', 'confirm' => I18N_SURE_TO_DELETE, 'target' => '_blank']
));

/**
 * Breadcrumb
 */
$this->Breadcrumbs->add(I18N_PHOTOS, ['_name' => 'albums']);
$this->Breadcrumbs->add($album->get('title'), $album->get('url'));

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
        $linkOptions = $baseOptions;
        if ($photo->has('description')) {
            $linkOptions += ['title' => $photo->get('description')];
        }
        //If Fancybox is enabled, adds some options
        if (getConfig('default.fancybox')) {
            $linkOptions += ['data-fancybox-href' => $this->Thumb->resizeUrl($photo->get('path'), ['height' => 1280])];
        }

        echo $this->Html->div('col-md-4 col-lg-3 mb-4', $this->element('MeCms.views/photo-preview', [
            'link' => $photo->get('url'),
            'path' => $photo->get('path'),
            'text' => $photo->get('description'),
        ] + compact('linkOptions')));
    }
    ?>
</div>

<?= $this->element('MeTools.paginator') ?>
