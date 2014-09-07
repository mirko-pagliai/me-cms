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
 * @package		MeCms\View\PostsCategories
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('posts', 'nav')); ?>
	
<div class="postsCategories index">
	<?php
		echo $this->Html->h2(__d('me_cms', 'Posts categories'));
		echo $this->Html->button(__d('me_cms', 'Add new'), array('action' => 'add'), array('class' => 'btn-success', 'icon' => 'plus'));
	?>
	<table class="table table-striped">
		<tr>
			<th><?php echo __d('me_cms', 'Title'); ?></th>
			<th><?php echo __d('me_cms', 'Parent'); ?></th>
			<th class="min-width text-center"><?php echo __d('me_cms', 'Posts'); ?></th>
		</tr>
		<?php foreach($postsCategories as $postsCategory): ?>
			<tr>
				<td>
					<?php
						$title = $this->Html->link($postsCategory['PostsCategory']['title'], array('action' => 'edit', $postsCategory['PostsCategory']['id']));
						echo $this->Html->div(NULL, $this->Html->strong($title));

						echo $this->Html->ul(array(
							$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $postsCategory['PostsCategory']['id']), array('icon' => 'pencil')),
							$this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $postsCategory['PostsCategory']['id']), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this posts category?')),					
							$this->Html->link(__d('me_cms', 'Open'), array('controller' => 'posts', 'action' => 'index', $postsCategory['PostsCategory']['slug'], 'admin' => FALSE, 'plugin' => 'me_cms_frontend'), array('icon' => 'external-link', 'target' => '_blank'))
						), array('class' => 'actions'));
					?>
				</td>
				<td><?php echo $this->Html->link($postsCategory['Parent']['title'], array('controller' => 'posts_categories', 'action' => 'view', $postsCategory['Parent']['id'])); ?></td>
				<td class="text-center"><?php echo $postsCategory['PostsCategory']['post_count']; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>