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

<div class="col-sm-12 col-md-6">
	<?php if(!empty($error)): ?>
		<div class="bg-danger text-danger"><?= $error ?></div>
	<?php elseif(!empty($file)): ?>
		<div class="bg-success text-success">
			<div class="col-sm-3"><?= $this->Thumb->img($file['target'], ['height' => 100]) ?></div>
			<div class="col-sm-9">
				<div><?= $this->Html->strong(basename($file['target'])) ?></div>
				<div><?= __d('me_cms', 'Directory: {0}', dirname(rtr($file['target']))) ?></div>
				<div><?= __d('me_cms', 'Type: {0}', $file['type']) ?></div>
				<div><?= __d('me_cms', 'Size: {0}', $this->Number->toReadableSize($file['size'])) ?></div>
			</div>
		</div>
	<?php endif; ?>
</div>