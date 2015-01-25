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
 * @package		MeCms\View\Systems
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('systems', 'nav')); ?>

<div class="systems index">
	<?php echo $this->Html->h2(__d('me_cms', 'Log viewer')); ?>
	
	<div class="well">
		<?php 
			echo $this->Form->createInline(FALSE, array('type' => 'get'));
			echo $this->Form->label('file', __d('me_cms', 'Log file'));
			echo $this->Form->input('file', array(
				'default'	=> empty($query['file']) ? NULL : $query['file'],
				'label'		=> __d('me_cms', 'Log file'),
				'name'		=> 'file',
				'onchange'	=> 'send_form(this)',
				'type'		=> 'select'
			));
			echo $this->Form->end(__d('me_cms', 'Select'), array(
				'div' => FALSE
			));
		?>
	</div>
	
	<?php
		if(!empty($log))
			echo $this->Html->pre($log, array('class' => 'pre-scrollable'));
	?>
</div>