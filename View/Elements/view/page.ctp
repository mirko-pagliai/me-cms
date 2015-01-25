<?php
/**
 * Page view element.
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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Elements\view
 */
?>

<div class="page-container content-container clearfix">
	<?php
		$content_header = NULL;

		if(!empty($page['Page']['title']))
			$content_header .= $this->Html->h3($this->Html->link($page['Page']['title'],
				am(array('controller' => 'pages', 'action' => 'view', 'plugin' => 'me_cms'), $params['pass'])),
				array('class' => 'content-title')
			);

		if(!empty($page['Page']['subtitle']))
			$content_header .= $this->Html->h4($this->Html->link($page['Page']['subtitle'],
				am(array('controller' => 'pages', 'action' => 'view', 'plugin' => 'me_cms'), $params['pass'])),
				array('class' => 'content-subtitle')
			);

		$content_info = NULL;

		if(!empty($page['Page']['created']))
			$content_info .= $this->Html->div('content-date',
				__d('me_cms', 'Posted on %s', $this->Time->format($page['Page']['created'], $config['datetime']['long'])), 
				array('icon' => 'clock-o')
			);

		if(!empty($content_info))
			$content_header .= $this->Html->div('content-info', $content_info);

		if(!empty($content_header))
			echo $this->Html->div('content-header', $content_header);
		
		if(!empty($page['Page']['text'])) {
			//If it was requested to truncate the text
			if(!empty($truncate))
				echo $this->Html->div('content-text', $truncate = $this->Text->truncate(
					$$page['Page']['text'], $config['truncate_to'], array('exact' => FALSE, 'html' => TRUE)
				));
			else
				echo $this->Html->div('content-text', $page['Page']['text']);
		}
		
		$content_buttons = NULL;

		//If it was requested to truncate the text and that has been truncated, it shows the "Read more" link
		if(!empty($truncate) && $truncate !== $page['Page']['text'])
			$content_buttons .= $this->Html->button(__d('me_cms', 'Read more'),
				am(array('controller' => 'pages', 'action' => 'view', 'plugin' => 'me_cms'), $params['pass']),
				array('class' => 'readmore')
			);

		if(!empty($content_buttons))
			echo $this->Html->div('content-buttons pull-right', $content_buttons);
	?>
</div>