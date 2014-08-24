<?php
/**
 * Common view used by post and posts category views.
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
 * @package		MeCmsBackend\View\Common
 */
?>

<?php
	$this->start('sidebar');
		echo $this->Html->li($this->Html->link(__d('me_cms', 'List posts'), array('controller' => 'posts', 'action' => 'index')));
		echo $this->Html->li($this->Html->link(__d('me_cms', 'Add post'), array('controller' => 'posts', 'action' => 'add')));
		echo $this->Html->li($this->Html->link(__d('me_cms', 'List categories'), array('controller' => 'posts_categories', 'action' => 'index')));
		echo $this->Html->li($this->Html->link(__d('me_cms', 'Add category'), array('controller' => 'posts_categories', 'action' => 'add')));
	$this->end();

	echo $this->fetch('content');
?>