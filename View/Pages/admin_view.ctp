<?php
/**
 * This file is part of MeTools.
 *
 * MeTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * MeTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with MeTools.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2014, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link		http://git.novatlantis.it Nova Atlantis Ltd
 * @package		MeCms\View\Pages
 */
?>
	
<?php $this->extend('/Common/pages'); ?>

<div class="pages view">
	<?php echo $this->Html->h2(__d('me_cms', 'Page')); ?>
	<?php echo $this->Html->para(NULL, sprintf('%s: %s', $this->Html->strong(__d('me_cms', 'Path')), $this->Html->code($page['Page']['path']))); ?>
	<pre class="pre-scrollable"><?php echo htmlspecialchars($page['Page']['content']); ?></pre>
</div>