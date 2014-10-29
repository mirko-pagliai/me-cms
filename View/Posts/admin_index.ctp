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
 * @package		MeCms\View\Posts
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('posts', 'nav')); ?>
	
<div class="posts index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'Posts'));
		echo $this->Html->button(__d('me_cms', 'Add new'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('title', __d('me_cms', 'Title')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('User.username', __d('me_cms', 'Author')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('category_id', __d('me_cms', 'Category')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('priority', __d('me_cms', 'Priority')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('created', __d('me_cms', 'Date')); ?></th>
		</tr>
		<?php foreach($posts as $post): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($post['Post']['title'], array('action' => 'edit', $id = $post['Post']['id']));
						
						//If the post is not active (it's a draft)
						if(!$post['Post']['active'])
							$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Draft'), array('class' => 'text-warning')));
						
						echo $this->Html->strong($title);
						
						$actions = array();
						
						//Only admins and managers can edit all posts
						//Users can edit only their own posts
						if($this->Auth->isManager() || $this->Auth->hasId($post['User']['id']))
							$actions[] = $this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $id), array('icon' => 'pencil'));					
									
						//Only admins and managers can delete posts
						if($this->Auth->isManager())
							$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $id), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this?'));
						
						$actions[] = $this->Html->link(__d('me_cms', 'Open'), array('action' => 'view', $post['Post']['slug'], 'admin' => FALSE), array('icon' => 'external-link', 'target' => '_blank'));
						
						echo $this->Html->ul($actions, array('class' => 'actions'));
					?>
				</td>
				<td class="text-center"><?php echo $post['User']['username']; ?></td>
				<td class="text-center"><?php echo $post['Category']['title']; ?></td>
				<td class="text-center">
					<?php
						switch($post['Post']['priority']) {
							case '1':
								echo $this->Html->badge('1', array('class' => 'priority-verylow', 'tooltip' => __d('me_cms', 'Very low')));
								break;
							case '2':
								echo $this->Html->badge('2', array('class' => 'priority-low', 'tooltip' => __d('me_cms', 'Low')));
								break;
							case '4':	
								echo $this->Html->badge('4', array('class' => 'priority-high', 'tooltip' => __d('me_cms', 'High')));
								break;
							case '5':
								echo $this->Html->badge('5', array('class' => 'priority-veryhigh', 'tooltip' => __d('me_cms', 'Very high')));
								break;
							default:
								echo $this->Html->badge('3', array('class' => 'priority-normal', 'tooltip' => __d('me_cms', 'Normal')));
								break;
						}
					?>
				</td>
				<td class="min-width text-center">
					<?php echo $this->Time->format($post['Post']['created'], $config['datetime']['short']); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>