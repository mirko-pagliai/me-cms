<?php
/**
 * Topbar.
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
 * @package		MeCmsBackend\View\Element
 */
?>

<nav class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only"><?php echo __d('me_cms_backend', 'Toggle navigation'); ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php echo $this->Html->link($config['site']['title'], '#', array('class' => 'navbar-brand')); ?>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<?php
				echo $this->Html->ul(array(
					$this->Html->link(NULL,	'/admin', array('icon' => 'home', 'title' => __d('me_cms_backend', 'Home'))),
					$this->Html->link(__d('me_cms_backend', 'Posts'),	array('controller' => 'posts',			'action' => 'index'),	array('icon' => 'thumb-tack')),
					$this->Html->link(__d('me_cms_backend', 'Pages'),	array('controller' => 'pages',			'action' => 'index'),	array('icon' => 'files-o')),
					$this->Html->link(__d('me_cms_backend', 'Photos'),	array('controller' => 'photos_albums',	'action' => 'index'),	array('icon' => 'image')),
					$this->Html->link(__d('me_cms_backend', 'Users'),	array('controller' => 'users',			'action' => 'index'),	array('icon' => 'users')),
				), array('class' => 'nav navbar-nav'));
			?>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<?php
						echo $this->Html->linkDropdown($auth['full_name'], array('icon' => 'user'));
						echo $this->Html->dropdown(array(
							$this->Html->link(__d('me_cms_backend', 'Change password'), array('controller' => 'users', 'action' => 'change_password')),
							$this->Html->link(__d('me_cms_backend', 'Logout'), array('controller' => 'users', 'action' => 'logout', 'admin' => FALSE))
						));
					?>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>
