<?php
/**
 * Uploader response.
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
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Elements\backend
 */
?>
	
<?php if(!empty($error)): ?>
	<div class="bg-danger text-danger padding-10 margin-10"><?php echo $error; ?></div>
<?php elseif(!empty($file)): ?>
	<div class="bg-success text-success padding-10 margin-10">
		<?php
			echo $this->Html->div(NULL, __d('me_cms', 'Successfully uploaded: %s', $this->Html->strong($file['target'])));
			echo $this->Html->div(NULL, __d('me_cms', 'Type: %s', $file['type']));
			echo $this->Html->div(NULL, __d('me_cms', 'Size: %s', $this->Number->toReadableSize($file['size'])));
		?>
	</div>
<?php endif; ?>