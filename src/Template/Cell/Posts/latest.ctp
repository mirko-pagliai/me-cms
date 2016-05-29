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
 * @copyright	Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 */
?>

<?php
	if(empty($posts)) {
		return;
    }
    
	//Extends the widget common view
	$this->extend('/Common/widget');
	$this->assign('title', count($posts) > 1 ? __d('me_cms', 'Latest {0} posts', count($posts)) : __d('me_cms', 'Latest post'));
			
    $posts = array_map(function($post) {
		return $this->Html->link($post->title, ['_name' => 'post', $post->slug]);
	}, $posts);
    
	echo $this->Html->ul($posts, ['icon' => 'caret-right']);
?>