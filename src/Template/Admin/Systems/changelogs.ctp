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
 * @author      Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright   Copyright (c) 2016, Mirko Pagliai for Nova Atlantis Ltd
 * @license     http://www.gnu.org/licenses/agpl.txt AGPL License
 * @link        http://git.novatlantis.it Nova Atlantis Ltd
 */

$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Changelogs'));
?>

<div class="well">
    <?= $this->Form->createInline(false, ['type' => 'get']) ?>
    <fieldset>
        <?php
            echo $this->Form->label('file', __d('me_cms', 'Changelog'));
            echo $this->Form->input('file', [
                'default' => $this->request->query('file'),
                'label' => __d('me_cms', 'Changelog'),
                'name' => 'file',
                'onchange' => 'send_form(this)',
            ]);
            echo $this->Form->submit(__d('me_cms', 'Select'));
        ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>

<?php
if (!empty($changelog)) {
    echo $this->Html->div(
        null,
        $this->Markdown->toHtml($changelog),
        ['id' => 'changelog']
    );
}