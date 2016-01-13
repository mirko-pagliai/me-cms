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

<nav id="topbar" class="navbar navbar-default" role="navigation">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#frontend-topbar-collapse">
				<span class="sr-only"><?= __d('me_cms', 'Toggle navigation') ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="frontend-topbar-collapse">
			<?= 
				$this->Html->ul([
					$this->Html->link(__d('me_cms', 'Home'),		['_name' => 'homepage'], ['icon' => 'home']),
					$this->Html->link(__d('me_cms', 'Categories'),	['_name' => 'posts_categories']),
					$this->Html->link(__d('me_cms', 'Pages'),		['_name' => 'pages']),
					$this->Html->link(__d('me_cms', 'Photos'),		['_name' => 'albums'])
				], ['class' => 'nav navbar-nav'])
			?>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>
