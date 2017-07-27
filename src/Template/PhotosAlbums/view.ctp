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
    $this->userbar($this->Html->span(__d('me_cms', 'Not published'), ['class' => 'label label-warning']));
}
$this->userbar($this->Html->link(
    __d('me_cms', 'Edit album'),
    ['action' => 'edit', $album->id, 'prefix' => ADMIN_PREFIX],
    ['icon' => 'pencil', 'target' => '_blank']
));
$this->userbar($this->Form->postLink(
    __d('me_cms', 'Delete album'),
    ['action' => 'delete', $album->id, 'prefix' => ADMIN_PREFIX],
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
$this->Breadcrumbs->add($title, ['_name' => 'album', $album->slug]);
?>

<div class="clearfix">
    <?php foreach ($photos as $photo) : ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="photo-box">
                <?php
                $text = implode(PHP_EOL, [
                    $this->Thumb->fit($photo->path, ['width' => 275]),
                    $this->Html->div('photo-info', $this->Html->div(
                        null,
                        $this->Html->para('small', $photo->description)
                    )),
                ]);

                $options = ['class' => 'thumbnail', 'title' => $photo->description];

                //If Fancybox is enabled, adds some options
                if (getConfig('default.fancybox')) {
                    $options = array_merge($options, [
                        'class' => 'fancybox thumbnail',
                        'data-fancybox-href' => $this->Thumb->resizeUrl($photo->path, ['height' => 1280]),
                        'rel' => 'group',
                    ]);
                }

                echo $this->Html->link($text, [
                    '_name' => 'photo',
                    'slug' => $album->slug,
                    'id' => $photo->id,
                ], $options);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->element('MeTools.paginator') ?>