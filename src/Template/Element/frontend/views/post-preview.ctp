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

<div class="content-preview">
	<a href="<?= \Cake\Routing\Router::url(['_name' => 'post', $post->slug]) ?>">
		<div>
			<div>
				<div class="content-title">
					<?= $this->Text->truncate($post->title, 40, ['exact' => FALSE]) ?>
				</div>
				<?php if(!empty($post->text)): ?>
					<div class="content-text">
						<?= $this->Text->truncate(strip_tags($post->text), 80, ['exact' => FALSE]) ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?= $this->Thumb->image($post->preview, ['side' => 205]) ?>
	</a>
</div>