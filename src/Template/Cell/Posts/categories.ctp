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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
	//Returns on categories index
	if($this->request->isCurrent(['_name' => 'posts_categories']))
		return;
?>

<?php if(!empty($categories) && count($categories) > 1): ?>
	<div class="widget sidebar-widget">
		<?php 
			echo $this->Html->h4(__d('me_cms', 'Posts categories'));
			echo $this->Form->create(FALSE, ['type' => 'get', 'url' => ['_name' => 'posts_category', 'category']]);
			echo $this->Form->input('q', [
				'empty'		=> __d('me_cms', 'Select a category'),
				'label'		=> FALSE,
				'onchange'	=> 'send_form(this)',
				'options'	=> $categories
			]);
			echo $this->Form->end();
		?>
	</div>
<?php endif; ?>