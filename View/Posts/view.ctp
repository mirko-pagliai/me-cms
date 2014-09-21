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

<div class="posts view">
	<div class="post-container clearfix">
		<div class="post-header">
			<?php
				$urlCategory = Router::url(array('action' => 'index', $post['Category']['slug']), TRUE);
				echo $this->Html->h4($this->Html->link($post['Category']['title'], $urlCategory), array('class' => 'post-category'));

				$urlPost = Router::url(array('controller' => 'posts', 'action' => 'view', $post['Post']['slug']), TRUE);
				echo $this->Html->h3($this->Html->link($post['Post']['title'], $urlPost), array('class' => 'post-title'));
			?>
			<div class="post-info">
				<?php
					if(!empty($post['User']['first_name']) && !empty($post['User']['last_name'])) {
						$fullName = sprintf('%s %s', $post['User']['first_name'], $post['User']['last_name']);
						echo $this->Html->div('post-author', __d('me_cms', 'Posted by %s', $fullName), array('icon' => 'user'));
					}

					if(!empty($post['Post']['created'])) {
						$created = $this->Time->format($post['Post']['created'], $config['datetime']['long']);
						echo $this->Html->div('post-created', __d('me_cms', 'Posted on %s', $created), array('icon' => 'clock-o'));
					}
				?>
			</div>
		</div>
		<?php echo $this->Html->div('post-content', $post['Post']['text']);	?>
	</div>
</div>