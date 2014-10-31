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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Elements\view
 */
?>

<div class="page-container clearfix">
	<div class="page-header">
		<?php
			if(!empty($page['Page']['title']))
				echo $this->Html->h3($this->Html->link($page['Page']['title'],
					am(array('controller' => 'pages', 'action' => 'view', 'plugin' => 'me_cms'), $this->request->params['pass'])),
					array('class' => 'page-title')
				);
			
			if(!empty($page['Page']['subtitle']))
				echo $this->Html->h4($this->Html->link($page['Page']['subtitle'],
					am(array('controller' => 'pages', 'action' => 'view', 'plugin' => 'me_cms'), $this->request->params['pass'])),
					array('class' => 'page-subtitle')
				);
		?>
		<div class="page-info">
			<?php
				if(!empty($page['Page']['created']))
					echo $this->Html->div('page-created',
						__d('me_cms', 'Posted on %s', $this->Time->format($page['Page']['created'], $config['datetime']['long'])), 
						array('icon' => 'clock-o')
					);
			?>
		</div>
	</div>
	<?php
		if(!empty($page['Page']['text'])) {
			//If it was requested to truncate the text
			if(!empty($truncate))
				echo $this->Html->div('page-content', $truncate = $this->Text->truncate(
					$$page['Page']['text'], $config['truncate_to'], array('exact' => FALSE, 'html' => TRUE)
				));
			else
				echo $this->Html->div('page-content', $page['Page']['text']);
		}
	?>
</div>
<div class="page-buttons pull-right">
	<?php
		//If it was requested to truncate the text and that has been truncated, it shows the "Read more" link
		if(!empty($truncate) && $truncate !== $page['Page']['text'])
			echo $this->Html->button(__d('me_cms', 'Read more'),
				am(array('controller' => 'pages', 'action' => 'view', 'plugin' => 'me_cms'), $this->request->params['pass']),
				array('class' => 'page-readmore')
			);
	?>
</div>