<?php
declare(strict_types=1);

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
 *
 * @var \MeCms\View\View\AdminView $this
 */

$this->Asset->css('MeCms.admin/uploader', ['block' => 'css_bottom']);
$this->Asset->script('/vendor/dropzone/dropzone', ['block' => 'script_bottom']);
?>

<?php $this->Html->scriptStart(['block' => 'script_bottom']); ?>
    Dropzone.autoDiscover = false;
    $(function() {
        $('.dropzone').dropzone({
            dictDefaultMessage: '<?= sprintf('%s %s', $this->Icon->icon('cloud-upload'), __d('me_cms', 'Drag files here or click')) ?>',
            <?php
            if (!empty($maxFiles)) {
                echo 'maxFiles: \'' . $maxFiles . '\',';
            }
            ?>
            previewsContainer: '#dropzone-preview',
            previewTemplate: '<div class="col-md-3 mb-4 dz-preview dz-file-preview">' +
                '<div class="card bg-light border-0 p-2">' +
                    '<ul class="list-group border-0 mb-3">' +
                        '<li class="dz-filename list-group-item py-1 px-2 text-center text-truncate" data-dz-name></li>' +
                        '<li class="dz-size list-group-item py-1 px-2 text-center" data-dz-size></li>' +
                    '</ul>' +
                    '<img class="card-img-bottom img-fluid mb-2" data-dz-thumbnail />' +
                    '<div class="progress dz-progress mb-3">' +
                        '<div class="progress-bar bg-success dz-upload" role="progressbar" data-dz-uploadprogress></div>' +
                    '</div>' +
                    '<div class="dz-success-mark text-center"><?= $this->Icon->icon('check') ?></div>' +
                    '<div class="dz-error-mark text-center"><?= $this->Icon->icon('close') ?></div>' +
                    '<div class="dz-error-message mt-2 text-danger text-center" data-dz-errormessage></div>' +
                '</div>' +
            '</div>',
        });
    });
<?php $this->Html->scriptEnd(); ?>

<?php
echo $this->Form->create(null, [
    'class' => 'dropzone text-center',
    'type' => 'file',
    'url' => ['?' => $this->getRequest()->getQueryParams(), '_ext' => 'json'],
]);
echo $this->Html->div('fallback', $this->Form->control('file', [
    'label' => false,
    'multiple' => 'multiple',
    'type' => 'file',
]));
echo $this->Form->end();
?>

<div id='dropzone-preview' class='row'></div>
