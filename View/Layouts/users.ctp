<?php
/**
 * Users layout.
 * This is used for some user actions, such as login or password recovery.
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
			
			echo $this->Layout->viewport();
			echo $this->Html->meta('favicon.ico', '/favicon.ico', array('type' => 'icon'));
			echo $this->fetch('meta');
			
			echo $this->Layout->css(array(
				'/MeCms/assets/users.min.css',
				'/MeTools/css/font-awesome.min'
			), array(
				'/MeTools/css/bootstrap.min',
				'/MeTools/css/font-awesome.min',
				'/MeTools/css/default',
				'/MeTools/css/forms',
				'/MeCms/css/users/layout'
			));
			echo $this->fetch('css');
			
			echo $this->Layout->js('/MeCms/assets/users.min.js', array(
				'/MeTools/js/jquery.min',
				'/MeTools/js/default'
			));
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<div id="content" class="container">
			<?php
				//Check if the logo image exists
				if(is_readable(WWW_ROOT.'img'.DS.$config['logo']))
					echo $this->Html->img($config['logo'], array('id' => 'logo'));
				
				echo $this->Session->flash();
				echo $this->fetch('content');
			?>
		</div>
		<?php
			echo $this->Html->div('container', $this->element('MeTools.sql_dump'));
			echo $this->fetch('script_bottom');
		?>
	</body>
</html>