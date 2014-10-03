<?php
/**
 * Common view for pages.
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

<div class="pages view">
	<div class="page-container clearfix">
		<div class="page-header">
			<?php
				if(!empty($this->fetch('title')))
					echo $this->Html->h3($this->Html->link($this->fetch('title'), 
						am(array('controller' => 'pages', 'action' => 'view'), $this->request->params['pass'])),
						array('class' => 'page-title')
					);
			?>
			<div class="page-info">
				<?php
					if(!empty($this->fetch('created')))
						echo $this->Html->div('page-created',
							__d('me_cms', 'Posted on %s', $this->Time->format($this->fetch('created'), $config['datetime']['long'])), 
							array('icon' => 'clock-o')
						);
				?>
			</div>
		</div>
		<?php
			if(!empty($this->fetch('content')))
				echo $this->Html->div('page-content', $this->fetch('content'));
		?>
	</div>
</div>