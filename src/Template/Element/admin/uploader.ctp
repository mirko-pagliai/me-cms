<?php
/**
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
$this->Asset->css(ME_CMS . '.admin/uploader', ['block' => 'css_bottom']);
$this->Asset->script('/vendor/dropzone/dropzone', ['block' => 'script_bottom']);
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

<?php
    echo $this->Form->create(null, [
        'class' => 'dropzone',
        'type' => 'file',
        'url' => ['?' => $this->request->getQuery(), '_ext' => 'json'],
    ]);
    echo $this->Html->div('fallback', $this->Form->control('file', [
        'label' => false,
        'multiple' => 'multiple',
        'type' => 'file',
    ]));
    echo $this->Form->end();
?>