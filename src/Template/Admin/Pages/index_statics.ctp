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
    $this->extend('/Admin/Common/index');
    $this->assign('title', __d('me_cms', 'Static pages'));
?>

<table class="table table-striped">
    <tr>
        <th><?= __d('me_cms', 'Filename') ?></th>
        <th class="text-center"><?= __d('me_cms', 'Title') ?></th>
        <th><?= __d('me_cms', 'Path') ?></th>
    </tr>
    <?php foreach($pages as $page): ?>
        <tr>
            <td>
                <strong><?= $this->Html->link($page->filename, ['_name' => 'page', $page->slug], ['target' => '_blank']) ?></strong>
                <?php
                    $actions = [
                        $this->Html->link(__d('me_cms', 'Open'), ['_name' => 'page', $page->slug], ['icon' => 'external-link', 'target' => '_blank']),
                    ];

                    echo $this->Html->ul($actions, ['class' => 'actions']);
                ?>
            </td>
            <td class="text-center">
                <?= $page->title ?>
            </td>
            <td>
                <samp><?= $page->path ?></samp>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</div>