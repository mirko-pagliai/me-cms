<?php
/**
 * Banner view element.
 * 
 * If you want to truncate the text, you have to pass the `$truncate` variable as `TRUE`.
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
 * @package		MeCms\View\Elements\view
 */
?>

<?php
	$classes = empty($banner['Position']['name']) ? NULL : sprintf('banner-%s', $banner['Position']['name']);
?>

<div class="banner <?php echo $classes; ?>">
	<?php
		$image = $this->Html->img($banner['Banner']['url'], array('class' => 'img-thumbnail'));
	
		if(!empty($banner['Banner']['target']))
			echo $this->Html->link($image, $banner['Banner']['target'], array(
				'target' => '_blank',
				'title'		=> empty($banner['Banner']['description']) ? NULL : $banner['Banner']['description']
			));
		else
			echo $image;
	?>
</div>