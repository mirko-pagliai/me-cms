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
			
			echo $this->Layout->css('MeCms./assets/frontend.min', [
				'/vendor/font-awesome/css/font-awesome.min',
				'MeCms.frontend/bootstrap.min',
				'MeTools.default',
				'MeTools.forms',
				'MeCms.frontend/layout',
				'MeCms.frontend/contents',
				'MeCms.frontend/photos'
			], ['block' => TRUE]);
			echo $this->fetch('css');
			
			echo $this->Layout->js('MeCms./assets/frontend.min', [
				'/vendor/jquery/jquery.min',
				'MeCms.frontend/bootstrap.min',
				'MeTools.default'
			], ['block' => TRUE]);
			
			if(is_readable(WWW_ROOT.'js'.DS.'frontend'.DS.'layout.js'))
				echo $this->Html->js('frontend/layout');
			
			echo $this->fetch('script');
		?>
	</head>
	<body>
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
			<?= $this->element('MeCms.frontend/topbar', [], ['cache' => TRUE]) ?>
		</header>
		<div class="container">
			<div class="row">
				<div id="content" class="col-sm-8 col-md-9">
					<?= $this->Flash->render() ?>
					<?= $this->fetch('content') ?>
				</div>
				<div id="sidebar" class="col-sm-4 col-md-3">
					<?= $this->fetch('sidebar') ?>
					<?= $this->allWidgets() ?>
				</div>
			</div>
		</div>
		<?php
			echo $this->element('MeCms.frontend/footer', [], ['cache' => TRUE]);
			
			if(config('frontend.analytics'))
				echo $this->Library->analytics(config('frontend.analytics'));
			
			if(config('shareaholic.site_id'));
				echo $this->Library->shareaholic(config('shareaholic.site_id'));
						
			echo $this->fetch('css_bottom');
			echo $this->fetch('script_bottom');
		?>
	</body>
</html>