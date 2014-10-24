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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Banners
 */
?>
	
<?php $this->assign('sidebar', $this->Menu->get('banners', 'nav')); ?>

<div class="banners view">
	<?php 
		echo $this->Html->h2(__d('me_cms', 'Banner'));
	
		echo $this->Html->ul(array(
			$this->Html->link(__d('me_cms', 'Edit'), array('action' => 'edit', $id = $banner['Banner']['id']), array('icon' => 'pencil')),
			$this->Form->postLink(__d('me_cms', 'Delete'), array('action' => 'delete', $id), array('class' => 'text-danger', 'icon' => 'trash-o'), __d('me_cms', 'Are you sure you want to delete this?'))
		), array('class' => 'actions'));
	?>
	
	<?php echo $this->Html->img($banner['Banner']['url']); ?>
	
	<dl class="dl-horizontal">
		<?php
			echo $this->Html->dt(__d('me_cms', 'Position'));
			echo $this->Html->dd($banner['Position']['name']);
			
			echo $this->Html->dt(__d('me_cms', 'Filename'));
			echo $this->Html->dd($banner['Banner']['filename']);
			
			if(!empty($banner['Banner']['target'])) {
				echo $this->Html->dt(__d('me_cms', 'Url'));
				echo $this->Html->dd($this->Html->link($banner['Banner']['target'], $banner['Banner']['target'], array('target' => '_blank')));
			}
			
			if(!empty($banner['Banner']['description'])) {
				echo $this->Html->dt(__d('me_cms', 'Description'));
				echo $this->Html->dd($banner['Banner']['description']);
			}
			
			echo $this->Html->dt(__d('me_cms', 'Status'));
			//If the banner is active (published)
			if($banner['Banner']['active'])
				echo $this->Html->dd(__d('me_cms', 'Published'), array('class' => 'text-success'));
			//Else, if the banner is not active (not published)
			else
				echo $this->Html->dd(__d('me_cms', 'Not published'), array('class' => 'text-warning'));
		?>
	</dl>
</div>