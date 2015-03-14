<?php
/**
 * Categories widget.
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
	//Returns on posts categories index
	if($params['controller'] == 'posts_categories' && $params['action'] == 'index' && $params['plugin'] == 'me_cms')
		return;
?>

<?php if(!empty($widgetsData['MeCms.categories'])): ?>
	<div class="widget sidebar-widget">
		<?php 
			echo $this->Html->h4(__d('me_cms', 'Categories'));
			echo $this->Form->create(FALSE, array('type' => 'get', 'url' => array('controller' => 'posts', 'action' => 'index', 'plugin' => 'me_cms')));
			echo $this->Form->input('q', array(
				'empty'		=> __d('me_cms', 'Select a category'),
				'label'		=> FALSE,
				'onchange'	=> 'send_form(this)',
				'options'	=> $widgetsData['MeCms.categories']
			));
			echo $this->Form->end();
		?>
	</div>
<?php endif; ?>