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

<div class="posts index">
	<?php foreach($posts as $post): ?>
		<div class="post-container clearfix">
			<div class="post-header">
				<?php
					echo $this->Html->h4(
						$this->Html->link($post['Category']['title'], array('action' => 'index', $post['Category']['slug'])),
						array('class' => 'post-category')
					);

					echo $this->Html->h3(
						$this->Html->link($post['Post']['title'], array('controller' => 'posts', 'action' => 'view', $post['Post']['slug'])),
						array('class' => 'post-title')
					);
				?>
				<div class="post-info">
					<?php
						if(!empty($post['User']['first_name']) && !empty($post['User']['last_name']))
							echo $this->Html->div('post-author',
								__d('me_cms', 'Posted by %s', sprintf('%s %s', $post['User']['first_name'], $post['User']['last_name'])),
								array('icon' => 'user')
							);
						
						if(!empty($post['Post']['created']))
							echo $this->Html->div('post-created',
								__d('me_cms', 'Posted on %s', $this->Time->format($post['Post']['created'], $config['datetime']['long'])),
								array('icon' => 'clock-o')
							);
					?>
				</div>
			</div>
			<?php
				echo $this->Html->div('post-content', $truncate = $this->Text->truncate(
					$post['Post']['text'], $config['truncate_to'], array('exact' => FALSE, 'html' => TRUE)
				));
			?>
			<div class="post-buttons pull-right">
				<?php
					//If the text has been truncated, it shows the "Read more" link
					if($truncate !== $post['Post']['text'])
						echo $this->Html->button(__d('me_cms', 'Read more'),
							array('controller' => 'posts', 'action' => 'view', $post['Post']['slug']),
							array('class' => 'post-readmore')
						);
				?>
			</div>
		</div>
	<?php endforeach; ?>
	<?php echo $this->element('MeTools.paginator'); ?>
</div>