<?php
/**
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\View\Pages
 */
?>
	
<?php $this->extend('/Common/pages'); ?>
	
<div class="pages index">
	<?php echo $this->Html->h2(__d('me_cms', 'Pages')); ?>
	<table class="table table-striped">
		<tr>
			<th><?php echo __d('me_cms', 'Filename'); ?></th>
		</tr>
		<?php foreach($pages as $page): ?>
		<tr>
			<td>
				<?php
					$title = $this->Html->link($filename = $page['Page']['filename'], array('action' => 'view', $id = $page['Page']['id']));
					echo $this->Html->div(NULL, $this->Html->strong($title));
					
					echo $this->Html->ul(array(
						$this->Html->link(__d('me_cms', 'View'), array('action' => 'view', $id), array('icon' => 'eye')),
						$this->Html->link(__d('me_cms', 'Open'), am(explode('/', $page['Page']['args']), array('action' => 'view', 'admin' => FALSE, 'plugin' => FALSE)), array('icon' => 'external-link', 'target' => '_blank'))
					), array('class' => 'actions'));
				?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>