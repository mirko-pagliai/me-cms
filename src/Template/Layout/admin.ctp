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
				'MeCms.admin/bootstrap.min',
				'MeTools.default',
				'MeTools.forms',
				'MeCms.admin/layout',
				'MeCms.admin/photos',
			], ['block' => TRUE]);
			echo $this->fetch('css');
			
			echo $this->Asset->js([
				'/vendor/jquery/jquery.min',
				'/vendor/js-cookie/js.cookie',
				'MeCms.admin/bootstrap.min',
				'MeTools.default',
				'MeCms.admin/layout',
			], ['block' => TRUE]);
			echo $this->fetch('script');
		?>
	</head>
	<body>
		<?= $this->element('MeCms.admin/topbar', [], [
            'cache' => [
                'key' => sprintf('topbar_user_%s', $this->Auth->user('id')),
                'config' => 'admin',
            ],
        ]) ?>
		<div class="container-fluid">
			<div class="row">
				<div id="sidebar" class="col-md-3 col-lg-2 hidden-xs hidden-sm affix-top">
					<?= $this->element('MeCms.admin/sidebar', [], [
                        'cache' => [
                            'key' => sprintf('sidebar_user_%s', $this->Auth->user('id')),
                            'config' => 'admin',
                        ],
                    ]) ?>
				</div>
				<div id="content" class="col-md-offset-3 col-lg-offset-2">
					<?= $this->Flash->render() ?>
					<?=	$this->fetch('content') ?>
				</div>
			</div>
		</div>
		<?= $this->fetch('css_bottom') ?>
		<?=	$this->fetch('script_bottom') ?>
	</body>
</html>