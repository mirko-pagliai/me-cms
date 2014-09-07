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
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Pages
 */
?>

<div class="pages view">
	<div class="post-container clearfix">
		<div class="post-header">
			<?php
				$urlPage = Router::url(array('controller' => 'pages', 'action' => 'view', $page['Page']['slug']), TRUE);
				echo $this->Html->h3($this->Html->link($page['Page']['title'], $urlPage), array('class' => 'post-title'));
			?>
			<div class="post-info">
				<?php
					if(!empty($page['Page']['created'])) {
						$created = $this->Time->format($page['Page']['created'], $config['datetime']['long']);
						echo $this->Html->div('post-created', __d('me_cms', 'Posted on %s', $created), array('icon' => 'clock-o'));
					}
				?>
			</div>
		</div>
		<?php echo $this->Html->div('post-content', $page['Page']['text']);	?>
	</div>
</div>

