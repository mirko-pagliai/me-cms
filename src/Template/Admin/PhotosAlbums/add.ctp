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

$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Add photos album'));
$this->Library->slugify();
?>

<?= $this->Form->create($album); ?>
<div class='float-form'>
    <?php
        echo $this->Form->input('active', [
            'checked' => true,
            'label' => sprintf('%s?', __d('me_cms', 'Published')),
        ]);
    ?>
</div>
<fieldset>
    <?php
        echo $this->Form->input('title', [
            'id' => 'title',
            'label' => __d('me_cms', 'Title'),
        ]);
        echo $this->Form->input('slug', [
            'id' => 'slug',
            'label' => __d('me_cms', 'Slug'),
            'help' => __d('me_cms', 'The slug is a string identifying a ' .
                'resource. If you do not have special needs, let it be ' .
                'generated automatically'),
        ]);
        echo $this->Form->input('description', [
            'label' => __d('me_cms', 'Description'),
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>