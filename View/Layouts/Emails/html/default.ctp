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
 * @package		MeCms\View\Emails\html
 */
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo $this->Html->title($config['title']); ?>
	</head>
	<body>
		<?php
			foreach(explode("\n", trim($this->fetch('content'))) as $line)
				echo $line.'<br />'.PHP_EOL;
			
			echo '<br />';
			
			echo $this->Html->small(__d('me_cms', 'This email was sent automatically from %s', $this->Html->link($config['title'], $site_address))).'<br />';
			echo $this->Html->small(__d('me_cms', 'Don\'t reply to this email'));
		?>
	</body>
</html>