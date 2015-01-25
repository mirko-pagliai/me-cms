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
 * @package		MeCms\View\PhotosAlbums
 */
?>

<?php
	if($config['fancybox']) {
		$this->Html->css('/MeCms/fancybox/fancybox.min', array('block' => 'css_bottom'));
		$this->Html->js('/MeCms/fancybox/fancybox.min.js', array('block' => 'script_bottom'));
	}
?>

<div class="photosAlbums index">
	<?php echo $this->Html->h2($album['PhotosAlbum']['title']); ?>
	<div class='clearfix'>
		<?php foreach($album['Photo'] as $photo): ?>
			<div class='col-sm-6 col-md-4'>
				<?php
					$text = $this->Html->thumb($photo['path'], array('side' => 270));
					$text .= $this->Html->div('info-wrapper', $this->Html->div('info', $this->Html->div('small', $photo['description'])));
					
					//If Fancybox is enabled, adds some link options
					if($config['fancybox'])
						$options = array(
							'class'					=> 'fancybox thumbnail',
							'data-fancybox-href'	=> $this->Html->thumbUrl($photo['path'], array('height' => 1280)),
							'rel'					=> 'group'
						);
					
					echo $this->Html->link($text, array('controller' => 'photos', 'action' => 'view', $photo['id']),
						am(array('class' => 'thumbnail', 'title' => $photo['description']), empty($options) ? array() : $options)
					);
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>