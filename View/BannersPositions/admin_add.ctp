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
 * @package		MeCms\View\BannersPositions
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('banners', 'nav')); ?>

<div class="bannersPositions form">
	<?php echo $this->Html->h2(__d('me_cms', 'Add banners position')); ?>
	<?php echo $this->Form->create('BannersPosition'); ?>
		<fieldset>
			<?php
				echo $this->Form->input('name', array(
					'label' => __d('me_cms', 'Name')
				));
				echo $this->Form->input('description', array(
					'label' => __d('me_cms', 'Description')
				));
			?>
		</fieldset>
	<?php echo $this->Form->end(__d('me_cms', 'Add banners position'), array('class' => 'will-be-disabled')); ?>
</div>