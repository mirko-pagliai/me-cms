<?php
/**
 * Latest posts widget.
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
	$params = $this->request->params;
	
	//Returns on the first page of posts index
	if($params['controller'] == 'posts' && $params['action'] == 'index' && $params['plugin'] == 'me_cms'
		&& !empty($params['paging']['Post']['page']) && $params['paging']['Post']['page'] == 1)
		return;

	//Gets the list of latest posts
	$posts = $this->requestAction(array('controller' => 'posts', 'action' => 'widget_latest', 'plugin' => 'me_cms', 10));
?>

<?php if(!empty($posts)): ?>
	<div class="widget sidebar-widget">
		<?php 
			echo $this->Html->h4(__d('me_cms', 'Latest posts'));
	
			$list = array();
			foreach($posts as $post)
				$list[] = $this->Html->link($post['Post']['title'],
					array('controller' => 'posts', 'action' => 'view', 'plugin' => 'me_cms', $post['Post']['slug']),
					array('class' => 'block no-wrap')
				);

			echo $this->Html->ul($list, array('icon' => 'caret-right'));
		?>
	</div>
<?php endif; ?>