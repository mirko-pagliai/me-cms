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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\PhotosAlbums
 */
?>

<?php $this->Html->css('/MeCms/css/frontend/photos'); ?>

<div class="photosAlbums index">
	<div class='clearfix'>
		<?php foreach($albums as $k => $album): ?>
			<?php if($k%3 === 0) echo '<div class=\'row\'>'; ?>
			<div class='col-md-4'>
				<div class='album-box'>
					<?php
						$thumb = $this->Html->thumb($album['Photo'][0]['path'], array('side' => '270'));
						echo $this->Html->link($thumb, array('action' => 'view', $album['PhotosAlbum']['slug']));
						
						echo $this->Html->para('album-title', $album['PhotosAlbum']['title']);
						echo $this->Html->para('album-photo-count', __d('me_cms', '%s photos', $album['PhotosAlbum']['photo_count']));
					?>
				</div>
			</div>
			<?php if($k%3 === 2 || $k +1 === count($albums)) echo '</div>'; ?>
		<?php endforeach; ?>
	</div>
</div>