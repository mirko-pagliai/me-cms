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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
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

			echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'));
			echo $this->Html->meta('favicon.ico', '/favicon.ico', array('type' => 'icon'));
			echo $this->Html->meta(__d('me_cms', 'Posts'), '/rss', array('type' => 'rss'));
			echo $this->fetch('meta');

			if(Configure::read('debug'))
				echo $this->Html->css(array(
					'/MeTools/css/font-awesome.min',
					'/MeTools/css/bootstrap.min',
					'/MeTools/css/default',
					'/MeTools/css/forms',
					'/MeCms/css/frontend/layout',
					'/MeCms/css/frontend/posts',
					'/MeCms/css/frontend/photos'
				), array('inline' => TRUE));
			else
				echo $this->Html->css('/MeCms/assets/frontend.min', array('inline' => TRUE));
			
			echo $this->fetch('css');
			
			if(Configure::read('debug'))
				echo $this->Html->js(array(
					'/MeTools/js/jquery.min',
					'/MeTools/js/bootstrap.min',
					'/MeTools/js/default'
				), array('inline' => TRUE));
			else
				echo $this->Html->js('/MeCms/assets/frontend.min', array('inline' => TRUE));
			
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<div id="header">
			<?php
				$logo = $this->Html->h1($config['title']);
				//Check if the logo image exists
				if(is_readable(WWW_ROOT.'img'.DS.$config['logo']))
					$logo = $this->Html->img($config['logo']);
				echo $this->Html->div('container', $this->Html->link($logo, '/', array('id' => 'logo')));		

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
							foreach($config['widgets'] as $widget) {
								if($this->elementExists($widget = sprintf('widgets/%s', $widget)))
									echo $this->element($widget);
							}
						?>
					</div>
				</div>
			<?php endif; ?>
			<?php
				echo $this->element('MeCms.frontend/footer', array(), array('cache' => TRUE));
				echo $this->element('MeTools.sql_dump');
				
				if(!empty($config['analytics']))
					echo $this->Library->analytics($config['analytics'], array('block' => 'script_bottom'));
				
				echo $this->fetch('script_bottom');
			?>
		</div>
	</body>
</html>
