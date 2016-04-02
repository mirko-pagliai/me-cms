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

<?php
	$this->Asset->css('MeCms.backend/uploader', ['block' => 'css_bottom']);
	$this->Asset->js('MeCms.backend/uploader', ['block' => 'script_bottom']);
?>

<div id="uploader">
	<div class="upload-area">
        <?= __d('me_cms', 'Drag here files to upload') ?>
        <div class="upload-icon"><?= $this->Html->icon('cloud-upload') ?></div>
	</div>
	<div class="upload-info">
		<div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
				<span class="sr-only">0% complete</span>
			</div>
		</div>
		<div class="upload-result row"></div>
		<div class="upload-error bg-danger text-danger padding-10 margin-10">
			<?= __d('me_cms', 'The file {0} exceeds the maximum limit', '<strong></strong>') ?>
		</div>
	</div>
</div>