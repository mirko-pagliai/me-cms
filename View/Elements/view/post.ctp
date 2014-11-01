<?php
/**
 * Post view element.
 * 
 * If you want to truncate the text, you have to pass the `$truncate` variable as `TRUE`.
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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Elements\view
 */
?>

<div class="post-container clearfix">
	<div class="content-header">
		<?php
			if(!empty($post['Category']['title']) && !empty($post['Category']['slug']))
				echo $this->Html->h5($this->Html->link($post['Category']['title'],
					array('controller' => 'posts', 'action' => 'index', 'plugin' => 'me_cms', $post['Category']['slug'])),
					array('class' => 'content-category')
				);

			if(!empty($post['Post']['title']) && !empty($post['Post']['slug']))
				echo $this->Html->h3($this->Html->link($post['Post']['title'],
					array('controller' => 'posts', 'action' => 'view', 'plugin' => 'me_cms', $post['Post']['slug'])),
					array('class' => 'content-title')
				);
			
			if(!empty($post['Post']['subtitle']) && !empty($post['Post']['slug']))
				echo $this->Html->h4($this->Html->link($post['Post']['subtitle'],
					array('controller' => 'posts', 'action' => 'view', 'plugin' => 'me_cms', $post['Post']['slug'])),
					array('class' => 'content-subtitle')
				);
		?>
		<div class="content-info">
			<?php
				if(!empty($post['User']['first_name']) && !empty($post['User']['last_name']))
					echo $this->Html->div('content-author',
						__d('me_cms', 'Posted by %s',
						sprintf('%s %s', $post['User']['first_name'], $post['User']['last_name'])),
						array('icon' => 'user')
					);

				if(!empty($post['Post']['created']))
					echo $this->Html->div('content-date',
						__d('me_cms', 'Posted on %s', $this->Time->format($post['Post']['created'], $config['datetime']['long'])),
						array('icon' => 'clock-o')
					);
			?>
		</div>
	</div>
	<?php
		if(!empty($post['Post']['text'])) {
			//If it was requested to truncate the text
			if(!empty($truncate))
				echo $this->Html->div('content-text', $truncate = $this->Text->truncate(
					$post['Post']['text'], $config['truncate_to'], array('exact' => FALSE, 'html' => TRUE)
				));
			else
				echo $this->Html->div('content-text', $post['Post']['text']);
		}
	?>
	<div class="content-buttons pull-right">
		<?php
			//If it was requested to truncate the text and that has been truncated, it shows the "Read more" link
			if(!empty($truncate) && $truncate !== $post['Post']['text'])
				echo $this->Html->button(__d('me_cms', 'Read more'),
					array('controller' => 'posts', 'action' => 'view', 'plugin' => 'me_cms', $post['Post']['slug']),
					array('class' => 'readmore')
				);
		?>
	</div>
</div>