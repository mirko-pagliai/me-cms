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

<div class="content-preview">
	<a href="<?= $this->Url->build(['_name' => 'post', $post->slug]) ?>">
		<div>
			<div>
				<div class="content-title">
					<?php
						if(isset($truncate['title']) && !$truncate['title']) {
							echo $post->title;
                        }
						else {
							echo $this->Text->truncate($post->title, empty($truncate['title']) ? 40 : $truncate['title'], ['exact' => FALSE]);
                        }
					?>
				</div>
				<?php if(!empty($post->text)): ?>
					<div class="content-text">
						<?php
							if(isset($truncate['text']) && !$truncate['text']) {
								echo strip_tags($post->text);
                            }
							else {
								echo $this->Text->truncate(strip_tags($post->text), empty($truncate['text']) ? 80 : $truncate['text'], ['exact' => FALSE]);
                            }
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?= $this->Thumb->image($post->preview, ['side' => 205]) ?>
	</a>
</div>