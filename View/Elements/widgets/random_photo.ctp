<?php
/**
 * Random photos widget.
 * 
 * This widget accepts the `limit` options, which allows you to set the 
 * number of photos to display.
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
	//Returns on photos album index
	if($params['controller'] == 'photos_albums' && $params['action'] == 'index' && $params['plugin'] == 'me_cms')
		return;
	
	//Returns on photos album view
	if($params['controller'] == 'photos_albums' && $params['action'] == 'view' && $params['plugin'] == 'me_cms')
		return;
	
	//Returns on photo view
	if($params['controller'] == 'photos' && $params['action'] == 'view' && $params['plugin'] == 'me_cms')
		return;
?>

<?php if(!empty($widgetsData['MeCms.random_photo'])): ?>
	<div class="widget sidebar-widget">
		<?php
			if(($count = count($widgetsData['MeCms.random_photo'])) > 1)
				echo $this->Html->h4(__d('me_cms', 'Random %d photos', $count));
			else
				echo $this->Html->h4(__d('me_cms', 'Random photo', $count));
			
			foreach($widgetsData['MeCms.random_photo'] as $photo)
				echo $this->Html->link(
					$this->Html->thumb($photo['Photo']['path'], array('side' => 263)), 
					array('controller' => 'photos_albums', 'action' => 'index', 'plugin' => 'me_cms'),
					array('class' => 'thumbnail')
				);
		?>
	</div>
<?php endif; ?>