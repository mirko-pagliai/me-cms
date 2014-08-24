<?php
/**
 * Admin layout.
 *
 * This file is part of MeCms Backend.
 *
 * MeCms Backend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeCms Backend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeCms Backend.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCmsBackend\View\Layouts
 */
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php 
			echo $this->Html->charset();

			if(empty($title_for_layout))
				echo $this->Html->title($config['site']['title']);
			else
				echo $this->Html->title(sprintf('%s - %s', $title_for_layout, $config['site']['title']));

			echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'));
			echo $this->fetch('meta');
			echo $this->Html->css(array(
				'/MeTools/css/font-awesome.min',
				'/MeTools/css/bootstrap.min',
				'/MeTools/css/default.min',
				'/MeTools/css/forms.min',
				'/MeCmsBackend/css/layouts/admin'
			), array('inline' => TRUE));
			echo $this->fetch('css');
			echo $this->Html->script(array(
				'/MeTools/js/jquery.min',
				'/MeTools/js/bootstrap.min',
				'/MeTools/js/default.min'
			), array('inline' => TRUE));
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<?php echo $this->element('topbar'); ?>
		<div class="container-fluid">
			<?php if($sidebar = $this->fetch('sidebar')): ?>
				<div class="row">
					<div id="sidebar" class="col-sm-3 col-md-2">
						<ul class="nav"><?php echo $sidebar; ?></ul>
					</div>
					<div id="content" class="col-sm-9 col-md-10">
						<?php 
							echo $this->Session->flash();
							echo $this->fetch('content');
							echo $this->element('MeTools.sql_dump');
						?>
					</div>
				</div>
			<?php else: ?>
				<?php 
					echo $this->Session->flash();
					echo $this->fetch('content');
					echo $this->element('MeTools.sql_dump');
				?>
			<?php endif; ?>
		</div>
	</body>
</html>
