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
	
<?php $this->assign('title', __d('me_cms', 'Add backup')); ?>

<div class="backups form">
	<?= $this->Html->h2(__d('me_cms', 'Add backup')) ?>
    <?= $this->Form->create($backup); ?>
    <fieldset>
        <?php
            echo $this->Form->input('filename', [
				'default'	=> 'backup_{$DATABASE}_{$DATETIME}.sql.gz',
				'label'		=> __d('me_cms', 'Filename')
			]);
        ?>
    </fieldset>
    <?= $this->Form->submit(__d('me_cms', 'Add backup')) ?>
    <?= $this->Form->end() ?>
</div>

<table class="table margin-0">
    <thead>
        <tr>
            <th><?= __d('me_cms', 'Pattern') ?></th>
            <th><?= __d('me_cms', 'Description') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="min-width"><code>{$DATABASE}</code></td>
            <td>
                <?= __d('me_cms', 'Database name') ?>.
            </td>
        </tr>
        <tr>
            <td class="min-width"><code>{$DATETIME}</code></td>
            <td>
                <?= __d('me_cms', 'Datetime. This is the equivalent of {0}', $this->Html->code('date(\'YmdHis\')')) ?>
            </td>
        </tr>
        <tr>
            <td class="min-width"><code>{$HOSTNAME}</code></td>
            <td>
                <?= __d('me_cms', 'Database hostname') ?>
            </td>
        </tr>
        <tr>
            <td class="min-width"><code>{$TIMESTAMP}</code></td>
            <td>
                <?= __d('me_cms', 'Timestamp. This is the equivalent of {0}', $this->Html->code('time()')) ?>
            </td>
        </tr>
    </tbody>
</table>