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
		<?= $this->Html->title(config('main.title')) ?>
	</head>
	<body>
		<?php
			foreach(explode("\n", trim($this->fetch('content'))) as $line)
				echo $line.'<br />'.PHP_EOL;
		?>
		<br />
		<small>
			<?= 
				__d('me_cms', 'This email was sent automatically from {0}',
					$this->Html->link(config('main.title'), \Cake\Routing\Router::url('/', TRUE)))
			?>
		</small><br />
		<small><?= __d('me_cms', 'The request has been sent from the IP {0}', $ip_address) ?></small><br />
		<small><?= __d('me_cms', 'Please, don\'t reply to this email') ?></small>		
	</body>
</html>