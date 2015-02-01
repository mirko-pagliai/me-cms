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

<div class="systems index">
	<?php echo $this->Html->h2(__d('me_cms', 'Media browser')); ?>
	<div id="type-form" class="well">
		<?php 
			echo $this->Form->createInline(FALSE, array('type' => 'get'));
			echo $this->Form->label('type', __d('me_cms', 'Type'));
			echo $this->Form->input('type', array(
				'default'	=> empty($query['type']) ? NULL : $query['type'],
				'onchange'	=> 'send_form(this)',
				'type'		=> 'select'
			));
			echo $this->Form->end(__d('me_cms', 'Select'), array(
				'div' => FALSE
			));
		?>
	</div>
	
	<?php
		if(!empty($kcfinder))
			echo $this->Html->iframe(array(
				'id'	=> 'kcfinder',
				'src'	=> $kcfinder,
				'width'	=> '100%'
			));
	?>
</div>