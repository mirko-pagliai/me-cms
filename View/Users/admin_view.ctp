<?php
/**
 * This file is part of MeTools.
 *
 * MeTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeTools.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Users
 */
?>
	
<?php $this->extend('/Common/users'); ?>

<div class="users view">
	<?php 
		echo $this->Html->h2(__d('me_cms', 'User'));
	
		echo $this->Html->ul(array(
			$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $user['User']['id']), array('icon' => 'pencil')),
			$this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $user['User']['id']), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this user?'))
		), array('class' => 'actions'));
	?>
	
	<dl class="dl-horizontal">
		<?php
			echo $this->Html->dt(__d('me_cms', 'Username'));
			echo $this->Html->dd($user['User']['username']);
			
			echo $this->Html->dt(__d('me_cms', 'Email'));
			echo $this->Html->dd($user['User']['email']);
			
			echo $this->Html->dt(__d('me_cms', 'Name'));
			echo $this->Html->dd($user['User']['full_name']);
			
			echo $this->Html->dt(__d('me_cms', 'Group'));
			echo $this->Html->dd($user['Group']['label']);
			
			echo $this->Html->dt(__d('me_cms', 'Status'));
			
			//If the user is banned
			if($user['User']['banned'])
				echo $this->Html->dd(__d('me_cms', 'Banned'), array('class' => 'text-danger'));
			//Else, if the user is pending (not active)
			elseif(!$user['User']['active'])
				echo $this->Html->dd(__d('me_cms', 'Pending'), array('class' => 'text-warning'));
			//Else, if the user is active
			else
				echo $this->Html->dd(__d('me_cms', 'Active'), array('class' => 'text-success'));
			
			if(!empty($user['User']['post_count'])) {
				echo $this->Html->dt(__d('me_cms', 'Post Count'));
				echo $this->Html->dd($user['User']['post_count']);
			}
			
			echo $this->Html->dt(__d('me_cms', 'Created'));
			echo $this->Html->dd($this->Time->format($user['User']['created'], $config['datetime']['long']));
		?>
	</dl>
</div>

<?php if(!empty($user['Post'])): ?>
	<div class="related">
		<?php echo $this->Html->h3(__d('me_cms', 'Related posts')); ?>
		<div class="btn-group pull-right margin-10">
			<?php echo $this->Html->linkButton(__d('me_cms', 'New post'), array('controller' => 'posts', 'action' => 'add'), array('icon' => 'plus')); ?>
		</div>
		
		<table class="table table-striped table-bordered">
			<tr>
				<th></th>
				<th><?php echo __d('me_cms', 'Id'); ?></th>
				<th><?php echo __d('me_cms', 'Category Id'); ?></th>
				<th><?php echo __d('me_cms', 'User Id'); ?></th>
				<th><?php echo __d('me_cms', 'Title'); ?></th>
				<th><?php echo __d('me_cms', 'Slug'); ?></th>
				<th><?php echo __d('me_cms', 'Text'); ?></th>
				<th><?php echo __d('me_cms', 'Priority'); ?></th>
				<th><?php echo __d('me_cms', 'Active'); ?></th>
				<th><?php echo __d('me_cms', 'Created'); ?></th>
				<th><?php echo __d('me_cms', 'Modified'); ?></th>
			</tr>
			<?php $i = 0; foreach($user['Post'] as $post): ?>
				<tr>
					<td class="actions">
						<?php echo $this->Html->linkButton(NULL, array('controller' => 'posts', 'action' => 'view', $post['id']), array('icon' => 'eye', 'tooltip' => __d('me_cms', 'View'))); ?>
						<?php echo $this->Html->linkButton(NULL, array('controller' => 'posts', 'action' => 'edit', $post['id']), array('icon' => 'pencil', 'tooltip' => __d('me_cms', 'Edit'))); ?>
						<?php echo $this->Form->postButton(NULL, array('controller' => 'posts', 'action' => 'delete', $post['id']), array('class' => 'btn-danger', 'icon' => 'trash-o', 'tooltip' => __d('me_cms', 'Delete')), __d('me_cms', 'Are you sure you want to delete this record?')); ?>
					</td>
					<td><?php echo $post['id']; ?></td>
					<td><?php echo $post['category_id']; ?></td>
					<td><?php echo $post['user_id']; ?></td>
					<td><?php echo $post['title']; ?></td>
					<td><?php echo $post['slug']; ?></td>
					<td><?php echo $post['text']; ?></td>
					<td><?php echo $post['priority']; ?></td>
					<td><?php echo $post['active']; ?></td>
					<td><?php echo $post['created']; ?></td>
					<td><?php echo $post['modified']; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
<?php endif; ?>