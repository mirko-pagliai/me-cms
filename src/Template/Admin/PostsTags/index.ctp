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

<?php $this->assign('title', __d('me_cms', 'Tags')); ?>

<div class="postsTags index">
	<?= $this->Html->h2(__d('me_cms', 'Tags')) ?>
	
	<div>
		<?php foreach($tags as $tag): ?>
			<div class="col-sm-3 margin-15">
				<?php
					$title = $this->Html->link($tag->tag, ['action' => 'edit', $tag->id]);
					echo sprintf('%s (%s)', $this->Html->strong($title), $tag->post_count);

					$actions = [];

					//Only admins and managers can edit tags
					if($this->Auth->isGroup(['admin', 'manager']))
						$actions[] = $this->Html->link(__d('me_cms', 'Edit'), ['action' => 'edit', $tag->id], ['icon' => 'pencil']);

					$actions[] = $this->Html->link(__d('me_cms', 'Open'), ['_name' => 'posts_tag', $tag->tag], ['icon' => 'external-link', 'target' => '_blank']);

					echo $this->Html->ul($actions, ['class' => 'actions']);
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<?= $this->element('MeTools.paginator') ?>
</div>