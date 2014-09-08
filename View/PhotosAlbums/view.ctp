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
		<?php foreach($album['Photo'] as $k => $photo): ?>
			<?php if($k%4 === 0) echo '<div class=\'row\'>'; ?>
			<div class='col-md-3'>
				<div class='photo-box'>
					<?php
						$thumb = $this->Html->thumb($photo['path'], array('side' => '270'));
						echo $this->Html->link($thumb, array('controller' => 'photos', 'action' => 'view', $photo['id']));
					?>
				</div>
			</div>
			<?php if($k%4 === 3 || $k +1 === count($album['Photo'])) echo '</div>'; ?>
		<?php endforeach; ?>
	</div>
</div>