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

<?php
	//Returns on pages index
	if($this->request->isCurrent(['_name' => 'pages']))
		return;
?>

<?php if(count($pages)): ?>
	<div class="widget sidebar-widget">
		<?php
			echo $this->Html->h4(__d('me_cms', 'Pages'));

			foreach($pages as $page)
				$list[] = $this->Html->link($page->title, ['_name' => 'page', $page->slug]);

			echo $this->Html->ul($list, ['icon' => 'caret-right']);
		?>
	</div>
<?php endif; ?>