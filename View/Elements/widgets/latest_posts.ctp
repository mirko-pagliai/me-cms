<?php
/**
 * Latest posts widget.
 * 
 * This widget accepts the `posts` options, which allows you to set the 
 * number of latest posts to display.
 *
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
 * @package		MeCms\View\Elements\widgets
 */
?>

<?php
	//Returns on the first page of posts index
	if($params['controller'] == 'posts' && $params['action'] == 'index' && $params['plugin'] == 'me_cms'
		&& !empty($params['paging']['Post']['page']) && $params['paging']['Post']['page'] == 1)
		return;
	
	//Gets the list of latest posts
	$posts = $this->requestAction(array('controller' => 'posts', 'action' => 'widget_latest', 'plugin' => 'me_cms', $limit = empty($options['posts']) ? 10 : $options['posts']));
?>

<?php if(!empty($posts)): ?>
	<div class="widget sidebar-widget">
		<?php
			if($limit > 1) {
				echo $this->Html->h4(__d('me_cms', 'Latest %d posts', $limit));
				
				$list = array();
				foreach($posts as $post)
					$list[] = $this->Html->link($post['Post']['title'],
						array('controller' => 'posts', 'action' => 'view', 'plugin' => 'me_cms', $post['Post']['slug']),
						array('class' => 'block no-wrap')
					);

				echo $this->Html->ul($list, array('icon' => 'caret-right'));
			}
			else {
				echo $this->Html->h4(__d('me_cms', 'Latest post'));
				echo $this->Html->link($posts[0]['Post']['title'],
					array('controller' => 'posts', 'action' => 'view', 'plugin' => 'me_cms', $posts[0]['Post']['slug']),
					array('class' => 'block no-wrap')
				);
			}
		?>
	</div>
<?php endif; ?>