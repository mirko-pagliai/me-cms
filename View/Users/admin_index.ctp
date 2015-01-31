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
 * @package		MeCms\View\Users
 */
?>
	
<div class="users index">
	<?php 
		echo $this->Html->h2(__d('me_cms', 'Users'));
		echo $this->Html->button(__d('me_cms', 'Add'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo $this->Paginator->sort('username', __d('me_cms', 'Username')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('full_name', __d('me_cms', 'Name')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('email', __d('me_cms', 'Email')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('group_id', __d('me_cms', 'Group')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('post_count', __d('me_cms', 'Posts')); ?></th>
			<th class="text-center"><?php echo $this->Paginator->sort('created', __d('me_cms', 'Date')); ?></th>
		</tr>
		<?php foreach($users as $user): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($user['User']['username'], array('action' => 'view', $id = $user['User']['id']));
						
						//If the user is banned
						if($user['User']['banned'])
							$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Banned'), array('class' => 'text-danger')));
						//Else, if the user is not active (pending)
						elseif(!$user['User']['active'])
							$title = sprintf('%s - %s', $title, $this->Html->span(__d('me_cms', 'Pending'), array('class' => 'text-warning')));
						
						echo $this->Html->strong($title);
						
						$actions = array(
							$this->Html->link(__d('me_cms', 'View'), array('action' => 'view', $id), array('icon' => 'eye')),
							$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $id), array('icon' => 'pencil'))
						);
						
						//Only admins can delete users
						if($this->Auth->isAdmin())
							$actions[] = $this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $id), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this?'));
						
						echo $this->Html->ul($actions, array('class' => 'actions'));
					?>
				</td>
				<td class="text-center">
					<?php echo $user['User']['full_name']; ?>
				</td>
				<td class="text-center">
					<?php echo $this->Html->link($email = $user['User']['email'], sprintf('mailto:%s', $email)); ?>
				</td>
				<td class="min-width text-center">
					<?php echo $user['Group']['label']; ?>
				</td>
				<td class="min-width text-center">
					<?php echo $user['User']['post_count']; ?>
				</td>
				<td class="min-width text-center">
					<?php echo $this->Time->format($user['User']['created'], $config['datetime']['short']); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>