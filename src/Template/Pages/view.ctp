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

<?php $this->append('userbar'); ?>
<li><?= $this->Html->link(__d('me_cms', 'Edit page'), ['action' => 'edit', $page->id, 'prefix' => 'admin'], ['icon' => 'pencil', 'target' => '_blank']) ?></li>
<li><?= $this->Form->postLink(__d('me_cms', 'Delete page'), ['action' => 'delete', $page->id, 'prefix' => 'admin'], ['icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?'), 'target' => '_blank']) ?></li>
<?php $this->end(); ?>
	
<?php $this->assign('title', $page->title); ?>

<div class="pages view">
	<?= $this->element('frontend/views/page', compact('page')); ?>
</div>