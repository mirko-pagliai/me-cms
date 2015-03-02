<?php
/**
 * Frontend layout.
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
 * @package		MeCms\View\Layouts
 */
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php 
			echo $this->Html->charset();

			if(empty($title_for_layout))
				echo $this->Html->title($config['title']);
			else
				echo $this->Html->title(sprintf('%s - %s', $title_for_layout, $config['title']));
			
			if(!empty($image_src)) {
				echo $this->Html->meta(array('href' => $image_src, 'rel' => 'image_src'));
				echo $this->Html->meta(array('content' => $image_src, 'property' => 'og:image'));
			}

			echo $this->Layout->viewport();
			echo $this->Html->meta('favicon.ico', '/favicon.ico', array('type' => 'icon'));
			echo $this->Html->meta(__d('me_cms', 'Latest posts'), '/posts/rss', array('type' => 'rss'));
			echo $this->fetch('meta');

			echo $this->Layout->css(array(
				'/MeCms/assets/frontend.min.css',
				'/MeTools/css/font-awesome.min'
			), array(
				'/MeTools/css/bootstrap.min',
				'/MeTools/css/font-awesome.min',
				'/MeTools/css/default',
				'/MeTools/css/forms',
				'/MeCms/css/frontend/layout',
				'/MeCms/css/frontend/contents',
				'/MeCms/css/frontend/photos'
			));
			echo $this->fetch('css');
			
			echo $this->Layout->js('/MeCms/assets/frontend.min.js', array(
				'/MeTools/js/jquery.min',
				'/MeTools/js/bootstrap.min',
				'/MeTools/js/default'
			));
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<div id="header">
			<?php
				$logo = $this->Html->h1($config['title']);
				//Check if the logo image exists
				if(is_readable(WWW_ROOT.'img'.DS.$config['frontend']['logo']))
					$logo = $this->Html->img($config['frontend']['logo']);
				echo $this->Html->div('container', $this->Html->link($logo, '/', array('id' => 'logo', 'title' => __d('me_cms', 'Homepage'))));		

				echo $this->element('MeCms.frontend/topbar', array(), array('cache' => TRUE));
			?>
		</div>
		<div class="container">
			<?php if($sidebar = $this->fetch('sidebar')): ?>
				<div class="row">
					<div id="content" class="col-sm-8 col-md-9">
						<?php
							echo $this->Session->flash();
							echo $this->fetch('content');
						?>
					</div>
					<div id="sidebar" class="col-sm-4 col-md-3">
						<ul class="nav"><?php echo $sidebar; ?></ul>
					</div>
				</div>
			<?php else: ?>
				<div class="row">
					<div id="content" class="col-sm-8 col-md-9">
						<?php 
							echo $this->Session->flash();
							echo $this->fetch('content');
						?>
					</div>
					<div id="sidebar" class="col-sm-4 col-md-3">
						<?php
							foreach($config['frontend']['widgets'] as $widget)
								echo $this->Widget->render($widget);
						?>
					</div>
				</div>
			<?php endif; ?>
			<?php
				echo $this->element('MeCms.frontend/footer', array(), array('cache' => TRUE));
				echo $this->element('MeTools.sql_dump');
			?>
		</div>
		<?php
			echo $this->Library->analytics($config['frontend']['analytics']);
			echo $this->fetch('css_bottom');
			echo $this->fetch('script_bottom');
		?>
	</body>
</html>
