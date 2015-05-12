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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\PhotosAlbums
 */
?>

<div class="photosAlbums index">
	<?php echo $this->Html->h2(__d('me_cms', 'Photos')); ?>
	<div class='clearfix'>
		<?php foreach($albums as $album): ?>
			<div class='col-sm-6 col-md-4'>
				<?php
					$thumb = $this->Html->thumb($album['Photo'][0]['path'], array('side' => 270));

					$info = $this->Html->div('strong', $album['PhotosAlbum']['title']);
					$info .= $this->Html->div('small', __d('me_cms', '%s photos', $album['PhotosAlbum']['photo_count']));
					$info = $this->Html->div('info-wrapper', $this->Html->div('info', $info));

					echo $this->Html->link($thumb.$info,
						array('action' => 'view', $album['PhotosAlbum']['slug']),
						array('class' => 'thumbnail', 'title' => $album['PhotosAlbum']['title'])
					);
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>