<?php
/**
 * Random photo widget.
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
 * @package		MeCms\View\Elements\widgets
 */
?>

<?php
	//Gets a random photo
	$photo = $this->requestAction(array('controller' => 'photos', 'action' => 'request_random', 'plugin' => 'me_cms'));
?>

<?php if(!empty($photo)): ?>
	<div class="widget sidebar-widget">
		<?php 
			echo $this->Html->h4(__d('me_cms', 'Random photo'));
			$thumb = $this->Html->thumb($photo['Photo']['path'], array('side' => 263));
			echo $this->Html->link($thumb, array('controller' => 'photos_albums', 'action' => 'index'), array('class' => 'thumbnail'));
		?>
	</div>
<?php endif; ?>