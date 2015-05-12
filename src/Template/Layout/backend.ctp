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
 */
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo $this->Html->charset();
			echo $this->Layout->viewport();
			echo $this->Html->title($this->fetch('title'));
			echo $this->Html->meta('icon');
			echo $this->fetch('meta');
			
			echo $this->Html->css([
				'MeTools.font-awesome.min',
				'MeCms.backend/bootstrap.min',
				'MeTools.default',
				'MeTools.forms',
				'MeCms.backend/layout',
				'MeCms.backend/photos'
			]);
			echo $this->fetch('css');
			
			echo $this->Html->js([
				'MeTools.jquery.min',
				'MeCms.backend/bootstrap.min',
				'MeCms.jquery.cookie',
				'MeTools.default',
				'MeCms.backend/layout'
			]);
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<?= $this->element('MeCms.backend/topbar') ?>
		<div class="container-fluid">
			<div class="row">
				<div id="sidebar" class="col-md-3 col-lg-2 hidden-xs hidden-sm affix-top">
					<?= $this->element('backend/sidebar') ?>
				</div>
				<div id="content" class="col-md-offset-3 col-lg-offset-2">
					<?php
						echo $this->Flash->render();
						echo $this->fetch('content');
					?>
				</div>
			</div>
		</div>
		<?php
			echo $this->fetch('css_bottom');
			echo $this->fetch('script_bottom');
		?>
	</body>
</html>