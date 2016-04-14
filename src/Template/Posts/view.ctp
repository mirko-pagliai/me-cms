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
<li><?= $this->Html->link(__d('me_cms', 'Edit post'), ['action' => 'edit', $post->id, 'prefix' => 'admin'], ['icon' => 'pencil', 'target' => '_blank']) ?></li>
<li><?= $this->Form->postLink(__d('me_cms', 'Delete post'), ['action' => 'delete', $post->id, 'prefix' => 'admin'], ['icon' => 'trash-o', 'confirm' => __d('me_cms', 'Are you sure you want to delete this?'), 'target' => '_blank']) ?></li>
<?php $this->end(); ?>

<?php
	$this->assign('title', $post->title);
	
	//Set some tags
	$this->Html->meta(['content' => 'article', 'property' => 'og:type']);
		
	if(!empty($post->preview)) {
		$this->Html->meta(['href' => $post->preview, 'rel' => 'image_src']);
		$this->Html->meta(['content' => $post->preview, 'property' => 'og:image']);
	}
	
	if(!empty($post->text)) {
		$this->Html->meta(['content' => $this->Text->truncate($this->BBCode->remove($post->text), 100, ['html' => TRUE]), 'property' => 'og:description']);
    }
?>

<div class="posts view">
	<?= $this->element('frontend/views/post', compact('post')); ?>
</div>