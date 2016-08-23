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

$this->Asset->css('MeCms.admin/uploader', ['block' => 'css_bottom']);
$this->Asset->js('/vendor/dropzone/dropzone', ['block' => 'script_bottom']);
?>

<?= $this->Html->scriptStart(); ?>
    Dropzone.autoDiscover = false;
    $(function() {
        $('.dropzone').dropzone({
            dictDefaultMessage: '<?= __d('me_cms', 'Drag files here or click') ?>',
            previewTemplate: '<div class="col-md-3 dz-preview dz-file-preview">' +
                '<div>' +
                    '<div class="dz-details">' +
                        '<div class="dz-filename" data-dz-name></div>' +
                        '<div class="dz-size" data-dz-size></div>' +
                        '<img data-dz-thumbnail />' +
                    '</div>' +
                    '<div class="progress dz-progress"><div class="progress-bar progress-bar-success dz-upload" role="progressbar" data-dz-uploadprogress></div></div>' +
                    '<div class="dz-success-mark"><?= $this->Html->icon('check') ?></div>' +
                    '<div class="dz-error-mark"><?= $this->Html->icon('close') ?></div>' +
                    '<div class="dz-error-message" data-dz-errormessage></div>' +
                '</div>' +
            '</div>',
        });
    });
<?= $this->Html->scriptEnd(); ?>

<?= $this->Form->create(null, ['class' => 'dropzone', 'type' => 'file']) ?>
    <div class="fallback">
        <?php
            echo $this->Form->input('file', [
                'label' => false,
                'multiple' => 'multiple',
                'type' => 'file',
            ]);
        ?>
    </div>
<?= $this->Form->end() ?>