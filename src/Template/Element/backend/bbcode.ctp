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

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingOne">
			<h4 class="panel-title">
				<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
					<?= __d('me_cms', 'BBCode') ?>
				</a>
			</h4>
		</div>
		<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
			<div class="panel-body">
				<table class="table margin-0">
					<thead>
						<tr>
							<th><?= __d('me_cms', 'BBCode tag') ?></th>
							<th><?= __d('me_cms', 'Description') ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="min-width">
								<code>[readmore /]</code>
							</td>
							<td>
								<?= __d('me_cms', 'Manually indicates where to cut the text and show the "Read more" button') ?>. 
								<?= __d('me_cms', 'If this is not present, the system can still cut the text, for example after a certain number of characters') ?>
							</td>
						</tr>
						<tr>
							<td class="min-width">
								<p><code>[youtube]https://youtu.be/bL_CJKq9rIw[/youtube]</code></p>
								<p><code>[youtube]bL_CJKq9rIw[/youtube]</code></p>
							</td>
							<td>
								<?= __d('me_cms', 'Adds a {0} video. You may indicate the ID or the url of the video', 'YouTube') ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>