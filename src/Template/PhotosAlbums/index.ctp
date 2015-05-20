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
 */
?>
	
<?php $this->assign('title', __d('me_cms', 'List photos albums')); ?>

<div class="photosAlbums index">
	<?= $this->Html->h2(__d('me_cms', 'List photos albums')) ?>
	<?php if(!empty($albums)): ?>
		<div class="clearfix">
			<?php foreach($albums as $album): ?>
				<div class="col-sm-6 col-md-4">
					<div class="photo-box">
						<?php
							$text = implode(PHP_EOL, [
								$this->Thumb->img($album->photos[0]->path, ['side' => 275]),
								$this->Html->div('photo-info', $this->Html->div(NULL, implode(PHP_EOL, [
									$this->Html->para('strong', $album->title),
									$this->Html->para('small', __d('me_cms', '{0} photos', $album->photo_count))
								])))
							]);
						
							echo $this->Html->link($text, ['_name' => 'album', $album->slug], ['class' => 'thumbnail', 'title' => $album->title]);
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>