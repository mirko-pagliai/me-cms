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
$this->assign('title', $title = $album->title);

if (config('default.fancybox')) {
    $this->Library->fancybox();
}

/**
 * Userbar
 */
if (!$album->active) {
    $this->userbar($this->Html->span(
        __d('me_cms', 'Not published'),
        ['class' => 'label label-warning']
    ));
}

$this->userbar([
    $this->Html->link(
        __d('me_cms', 'Edit album'),
        ['action' => 'edit', $album->id, 'prefix' => 'admin'],
        ['icon' => 'pencil', 'target' => '_blank']
    ),
    $this->Form->postLink(
        __d('me_cms', 'Delete album'),
        ['action' => 'delete', $album->id, 'prefix' => 'admin'],
        [
            'icon' => 'trash-o',
            'confirm' => __d('me_cms', 'Are you sure you want to delete this?'),
            'target' => '_blank',
        ]
    ),
]);

/**
 * Breadcrumb
 */
$this->Breadcrumb->add(__d('me_cms', 'Photos'), ['_name' => 'albums']);
$this->Breadcrumb->add($title, ['_name' => 'album', $album->slug]);
?>

<div class="clearfix">
    <?php foreach ($photos as $photo) : ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="photo-box">
                <?php
                    $text = implode(PHP_EOL, [
                        $this->Thumb->crop($photo->path, ['width' => 275]),
                        $this->Html->div('photo-info', $this->Html->div(
                            null,
                            $this->Html->para('small', $photo->description)
                        )),
                    ]);

                    $options = [
                        'class' => 'thumbnail',
                        'title' => $photo->description,
                    ];

                    //If Fancybox is enabled, adds some options
                    if (config('default.fancybox')) {
                        $options = am($options, [
                            'class' => 'fancybox thumbnail',
                            'data-fancybox-href' => $this->Thumb->resizeUrl($photo->path, ['height' => 1280]),
                            'rel' => 'group',
                        ]);
                    }

                    echo $this->Html->link(
                        $text,
                        [
                            '_name' => 'photo',
                            'slug' => $album->slug,
                            'id' => $photo->id
                        ],
                        $options
                    );
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->element('MeTools.paginator') ?>