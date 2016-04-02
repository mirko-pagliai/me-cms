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

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo $this->Html->charset();
			echo $this->Html->viewport();
			echo $this->Html->title($this->fetch('title'));
			echo $this->fetch('meta');
			
			echo $this->Html->css('https://fonts.googleapis.com/css?family=Roboto', ['block' => TRUE]);
			echo $this->Asset->css([
				'/vendor/font-awesome/css/font-awesome.min',
				'MeCms.frontend/bootstrap.min',
				'MeTools.default',
				'MeTools.forms',
				'MeCms.frontend/layout',
				'MeCms.frontend/contents',
				'MeCms.frontend/photos'
			], ['block' => TRUE]);
			echo $this->fetch('css');
			
			echo $this->Asset->js([
				'/vendor/jquery/jquery.min',
				'MeCms.frontend/bootstrap.min',
				'/vendor/jquery-cookie/jquery.cookie',
				'MeTools.default',
				'MeCms.frontend/layout'
			], ['block' => TRUE]);
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<?= $this->element('MeCms.frontend/cookies_policy') ?>
		<header>
			<div class="container">
				<?php
					$logo = $this->Html->h1(config('main.title'));

					//Check if the logo image exists
					if(is_readable(WWW_ROOT.'img'.DS.config('frontend.logo')))
						$logo = $this->Html->img(config('frontend.logo'));

					echo $this->Html->link($logo, '/', ['id' => 'logo', 'title' => __d('me_cms', 'Homepage')]);		
				?>
			</div>
			<?= $this->element('MeCms.frontend/topbar', [], ['cache' => ['key' => 'topbar', 'config' => 'frontend']]) ?>
		</header>
		<div class="container">
			<div class="row">
				<div id="content" class="col-sm-8 col-md-9">
					<?= $this->Flash->render() ?>
					<?= $this->fetch('content') ?>
				</div>
				<div id="sidebar" class="col-sm-4 col-md-3">
					<?= $this->fetch('sidebar') ?>
					<?= $this->Widget->all() ?>
				</div>
			</div>
		</div>
		<?php
			echo $this->element('MeCms.frontend/footer', [], ['cache' => ['key' => 'footer', 'config' => 'frontend']]);
			echo $this->fetch('css_bottom');
			echo $this->fetch('script_bottom');
		?>
	</body>
</html>