<?php
/**
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\View\Posts
 */
?>
	
<?php $this->extend('/Common/posts'); ?>
	
<div class="posts index">
	<?php
		echo $this->Html->h2(__d('me_cms_backend', 'Posts'));
		echo $this->Html->button(__d('me_cms_backend', 'Add new'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('user_id', __d('me_cms_backend', 'Author')); ?></th>
			<th><?php echo $this->Paginator->sort('category_id'); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('priority'); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('created', __d('me_cms_backend', 'Date')); ?></th>
		</tr>
		<?php foreach($posts as $post): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($post['Post']['title'], array('action' => 'edit', $post['Post']['id']));
						
						//If the post is not active (it's a draft)
						if(!$post['Post']['active'])
							$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms_backend', 'Draft'), array('class' => 'text-warning')));
						
						echo $this->Html->strong($title);
						
						echo $this->Html->ul(array(
							$this->Html->link(__d('me_cms_backend', 'Edit'), array('action' => 'edit', $post['Post']['id']), array('icon' => 'pencil')),
							$this->Form->postLink(__d('me_cms_backend', 'Delete'), array('action' => 'delete', $post['Post']['id']), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms_backend', 'Are you sure you want to delete this post?')),
							$this->Html->link(__d('me_cms_backend', 'Open'), array('action' => 'view', 'admin' => FALSE, 'plugin' => 'me_cms_frontend', $post['Post']['slug']), array('icon' => 'external-link', 'target' => '_blank'))
						), array('class' => 'actions'));
					?>
				</td>
				<td class="text-center">
					<?php echo $post['User']['username']; ?>
				</td>
				<td><?php echo $post['Category']['title']; ?></td>
				<td class="text-center">
					<?php
						switch($post['Post']['priority']) {
							case '1':
								echo $this->Html->badge('1', array('class' => 'priority-verylow', 'tooltip' => __d('me_cms_backend', 'Very low')));
								break;
							case '2':
								echo $this->Html->badge('2', array('class' => 'priority-low', 'tooltip' => __d('me_cms_backend', 'Low')));
								break;
							case '4':	
								echo $this->Html->badge('4', array('class' => 'priority-high', 'tooltip' => __d('me_cms_backend', 'High')));
								break;
							case '5':
								echo $this->Html->badge('5', array('class' => 'priority-veryhigh', 'tooltip' => __d('me_cms_backend', 'Very high')));
								break;
							default:
								echo $this->Html->badge('3', array('class' => 'priority-normal', 'tooltip' => __d('me_cms_backend', 'Normal')));
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