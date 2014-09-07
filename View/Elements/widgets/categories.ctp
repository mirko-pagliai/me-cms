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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Elements\backend\widgets
 */
?>

<?php
	//Gets the categories
	$categories = $this->requestAction(array('controller' => 'posts_categories', 'action' => 'request_list'));
?>

<?php if(!empty($categories)): ?>
	<div class="widget sidebar-widget">
		<?php 
			echo $this->Html->h4(__d('me_cms', 'Categories'));
			echo $this->Form->create(FALSE, array('type' => 'get'));
			echo $this->Form->input('category', array(
				'empty'		=> __d('me_cms', 'Select a category'),
				'label'		=> FALSE,
				'onchange'	=> 'send_form(this)',
				'options'	=> $categories
			));
			echo $this->Form->end();
		?>
	</div>
<?php endif; ?>