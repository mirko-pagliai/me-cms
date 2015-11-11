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
			
			echo $this->Layout->css([
				'MeCms./assets/login.min',
				'/vendor/font-awesome/css/font-awesome.min'
			], [
				'/vendor/font-awesome/css/font-awesome.min',
				'MeCms.login/bootstrap.min',
				'MeTools.default',
				'MeTools.forms',
				'MeCms.login/layout'
			], ['block' => TRUE]);
			echo $this->fetch('css');
			
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<div id="content" class="container">
			<?php
				//Check if the logo image exists
				if(is_readable(WWW_ROOT.'img'.DS.config('frontend.logo')))
					echo $this->Html->img(config('frontend.logo'), ['id' => 'logo']);
				
				echo $this->Flash->render();
				echo $this->Flash->render('auth');
				echo $this->fetch('content');
			?>
		</div>
		<?= $this->fetch('css_bottom') ?>
		<?=	$this->fetch('script_bottom') ?>
	</body>
</html>