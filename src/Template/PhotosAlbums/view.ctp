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
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
    $this->extend('/Common/view');
    $this->assign('title', __d('me_cms', 'Album {0}', $album->title));
    
	if(config('frontend.fancybox')) {
		$this->Library->fancybox();
    }
?>

<div class="clearfix">
    <?php foreach($album->photos as $photo): ?>
        <div class="col-sm-6 col-md-4">
            <div class="photo-box">
                <?php
                    $text = implode(PHP_EOL, [
                        $this->Thumb->square($photo->path, ['side' => 275, 'force' => TRUE]),
                        $this->Html->div('photo-info', $this->Html->div(NULL, $this->Html->para('small', $photo->description))),
                    ]);

                    $options = [];
                    
                    //If Fancybox is enabled, adds some options
                    if(config('frontend.fancybox')) {
                        $options = [
                            'class' => 'fancybox thumbnail',
                            'data-fancybox-href' => $this->Thumb->url($photo->path, ['height' => 1280]),
                            'rel' => 'group',
                        ];
                    }

                    echo $this->Html->link($text, ['_name' => 'photo', 'slug' => $album->slug, 'id' => $photo->id], am([
                        'class' => 'thumbnail',
                        'title' => $photo->description,
                    ], $options));
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>