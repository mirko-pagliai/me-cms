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
	if($this->request->isAction('view') && $post->preview) {
		$this->Html->meta(['href' => $post->preview, 'rel' => 'image_src']);
		$this->Html->meta(['content' => $post->preview, 'property' => 'og:image']);
	}
?>

<div class="post-container content-container">
	<div class="content-header">
		<?php
			if(!empty($post->category->title) && !empty($post->category->slug))
				echo $this->Html->h5($this->Html->link($post->category->title, ['_name' => 'category', $post->category->slug]), ['class' => 'content-category']);

			echo $this->Html->h3($this->Html->link($post->title, ['_name' => 'post', $post->slug]), ['class' => 'content-title']);

			if(!empty($post->subtitle))
				echo $this->Html->h4($this->Html->link($post->subtitle, ['_name' => 'post', $post->slug]), ['class' => 'content-subtitle']);
		?>
		<div class="content-info">
			<?php
				if(!empty($post->user->full_name))
					echo $this->Html->div('content-author',
						__d('me_cms', 'Posted by {0}', $post->user->full_name),
						['icon' => 'user']
					);

				if(!empty($post->created))
					echo $this->Html->div('content-date',
						__d('me_cms', 'Posted on {0}', $post->created->i18nFormat(config('main.datetime.long'))),
						['icon' => 'clock-o']
					);
			?>
		</div>
	</div>
	<div class="content-text">
		<?php
			//If it was requested to truncate the text
			if(!$this->request->isAction('view') && config('frontend.truncate_to'))
				echo $truncate = $this->Text->truncate($post->text, config('frontend.truncate_to'), ['exact' => FALSE, 'html' => TRUE]);
			else
				echo $post->text;
		?>
	</div>
	<div class="content-buttons">
		<?php
			//If it was requested to truncate the text and that has been truncated, it shows the "Read more" link
			if(!empty($truncate) && $truncate !== $post->text)
				echo $this->Html->button(__d('me_cms', 'Read more'), ['_name' => 'post', $post->slug], ['class' => ' readmore']);
		?>
	</div>
</div>