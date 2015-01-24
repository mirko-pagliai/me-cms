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

<?php $this->Html->scriptStart(); ?>
	//Function to resize the iframe
	function resizeKcfinder() {
		var maxHeight = $(window).height() - $('#topbar').outerHeight(true) -$('#type-form').outerHeight(true) -$('#footer').outerHeight(true) - 20;
		$('#kcfinder').height(maxHeight);
	}
	
	$(function() {
		//Resizes the iframe on load
		resizeKcfinder();
		
		//Resizes the iframe on window resize
		$(window).resize(resizeKcfinder);
	});
<?php $this->Html->scriptEnd(); ?>

<div class="systems index">
	<div id="type-form" class="well">
		<?php 
			echo $this->Form->createInline(FALSE, array('type' => 'get'));
			echo $this->Form->label('type', __d('me_cms', 'Type'));
			echo $this->Form->input('type', array(
				'default'	=> empty($this->request->query['type']) ? NULL : $this->request->query['type'],
				'onchange'	=> 'send_form(this)',
				'type'		=> 'select'
			));
			echo $this->Form->end(__d('me_cms', 'Select'), array(
				'class'	=> 'will-be-disabled',
				'div'	=> FALSE
			));
		?>
	</div>
	
	<?php
		if(!empty($kcfinder))
			echo $this->Html->iframe(array(
				'height'	=> '550',
				'id'		=> 'kcfinder',
				'src'		=> $kcfinder,
				'width'		=> '100%'
			));
	?>
</div>