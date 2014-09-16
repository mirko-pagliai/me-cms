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
	
<?php $this->assign('sidebar', $this->Menu->get('photos', 'nav')); ?>
	
<div class="photosAlbums index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'Photos albums'));
		echo $this->Html->button(__d('me_cms', 'Add new'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th class="min-width text-center">
				<?php echo $this->Paginator->sort('photo_count', __d('me_cms', 'Photos')); ?>
			</th>
		</tr>
		<?php foreach($photosAlbums as $photosAlbum): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($photosAlbum['PhotosAlbum']['title'], array('controller' => 'photos', $id = $photosAlbum['PhotosAlbum']['id']));
					
						//If the photos album is not active (not published)
						if(!$photosAlbum['PhotosAlbum']['active'])
							$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Not published'), array('class' => 'text-warning')));
						
						echo $this->Html->strong($title);
						
						echo $this->Html->ul(array(
							$this->Html->link(__d('me_cms', 'View'), array('controller' => 'photos', $id), array('icon' => 'eye')),
							$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $id), array('icon' => 'pencil')),
							$this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $id), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this  photos album?')),
							$this->Html->link(__d('me_cms', 'Open'), array('action' => 'view', $photosAlbum['PhotosAlbum']['slug'], 'admin' => FALSE), array('icon' => 'external-link', 'target' => '_blank'))
						), array('class' => 'actions'));
					?>
				</td>
				<td class="min-width text-center"><?php echo $photosAlbum['PhotosAlbum']['photo_count']; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>