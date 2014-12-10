<?php
/**
 * Frontend topbar.
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
 * @package		MeCms\View\Elements\frontend
 */
?>

<nav id="topbar" class="navbar navbar-default" role="navigation">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#frontend-topbar-collapse">
				<span class="sr-only"><?php echo __d('me_cms', 'Toggle navigation'); ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="frontend-topbar-collapse">
			<ul class="nav navbar-nav">
				<?php
					echo $this->Html->li($this->Html->link(__d('me_cms', 'Home'), '/',	array('icon' => 'home')));
					echo $this->Html->li($this->Html->link(__d('me_cms', 'Categories'), array('controller' => 'posts_categories',	'action' => 'index', 'plugin' => 'me_cms')));
					echo $this->Html->li($this->Html->link(__d('me_cms', 'Pages'),		array('controller' => 'pages',				'action' => 'index', 'plugin' => 'me_cms')));
					echo $this->Html->li($this->Html->link(__d('me_cms', 'Photos'),		array('controller' => 'photos_albums',		'action' => 'index', 'plugin' => 'me_cms')));
				?>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>
