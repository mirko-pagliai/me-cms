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
$this->assign('title', $title = __d('me_cms', 'Edit photo'));
?>

<?= $this->Form->create($photo); ?>
<div class='float-form'>
    <?php
        echo $this->Form->control('album_id', [
            'label' => __d('me_cms', 'Album'),
        ]);
        echo $this->Form->control('active', [
            'label' => sprintf('%s?', __d('me_cms', 'Published')),
        ]);
    ?>
</div>
<fieldset>
    <p><?= $this->Html->strong(__d('me_cms', 'Preview')) ?></p>
    <?php
        echo $this->Thumb->resize(
            $photo->path,
            ['width' => 1186],
            ['class' => 'img-thumbnail margin-15']
        );

        echo $this->Form->control('filename', [
            'disabled' => true,
            'label' => __d('me_cms', 'Filename'),
        ]);
        echo $this->Form->control('description', [
            'label' => __d('me_cms', 'Description'),
            'rows' => 3,
            'type' => 'textarea',
        ]);
    ?>
</fieldset>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>