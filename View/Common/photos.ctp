<?php
/**
 * Common view used by photos and photos albums views.
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
 * @package		MeCms\View\Common
 */
?>

<?php
	$this->start('sidebar');
		echo $this->Html->li($this->Html->link(__d('me_cms', 'Add photos'),		array('controller' => 'photos',			'action' => 'add')));
		echo $this->Html->li($this->Html->link(__d('me_cms', 'List albums'),	array('controller' => 'photos_albums',	'action' => 'index')));
		echo $this->Html->li($this->Html->link(__d('me_cms', 'Add album'),		array('controller' => 'photos_albums',	'action' => 'add')));
	$this->end();

	echo $this->fetch('content');
?>