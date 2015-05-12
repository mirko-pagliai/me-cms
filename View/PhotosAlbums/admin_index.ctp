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
	<?php
		echo $this->Html->h2(__d('me_cms', 'Photos albums'));
		echo $this->Html->button(__d('me_cms', 'Add'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th class="min-width text-center">
				<?php echo $this->Paginator->sort('photo_count', __d('me_cms', 'Photos')); ?>
			</th>
		</tr>
		<?php foreach($albums as $album): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($album['PhotosAlbum']['title'], array('controller' => 'photos', $id = $album['PhotosAlbum']['id']));
					
						//If the album is not active (not published)
						if(!$album['PhotosAlbum']['active'])
							$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Not published'), array('class' => 'text-warning')));
						
						echo $this->Html->strong($title);
						
						$actions = array(
							$this->Html->link(__d('me_cms', 'View'), array('controller' => 'photos', $id), array('icon' => 'eye')),
							$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $id), array('icon' => 'pencil'))
						);
						
						//Only admins and managers can delete albums
						if($this->Auth->isManager())
							$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $id), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this?'));

						$actions[] = $this->Html->link(__d('me_cms', 'Open'), array('action' => 'view', $album['PhotosAlbum']['slug'], 'admin' => FALSE), array('icon' => 'external-link', 'target' => '_blank'));
						
						echo $this->Html->ul($actions, array('class' => 'actions'));
					?>
				</td>
				<td class="min-width text-center"><?php echo $album['PhotosAlbum']['photo_count']; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>