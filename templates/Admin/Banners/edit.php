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
 */
$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Edit banner'));
?>

<?= $this->Form->create($banner); ?>
<div class="row">
    <div class="col-lg-3 order-12">
        <div class="float-form">
        <?php
        echo $this->Form->control('position_id', [
            'label' => I18N_POSITION,
        ]);
        echo $this->Form->control('active', [
            'label' => I18N_PUBLISHED,
        ]);
        echo $this->Form->control('thumbnail', [
            'help' => __d('me_cms', 'The banner is displayed as a  thumbnail. ' .
                'You should disable this, if the banner is an animated gif'),
            'label' => __d('me_cms', 'Thumbnail'),
        ]);
        ?>
        </div>
    </div>
    <fieldset class="col-lg-9">
        <div class="mb-2">
            <strong><?= I18N_PREVIEW ?></strong>
        </div>
        <?php
        $class = 'img-thumbnail mb-3';
        if ($banner->thumbnail) {
            echo $this->Thumb->resize($banner->path, ['width' => 1186], compact('class'));
        } else {
            echo $this->Html->img($banner->www, compact('class'));
        }

        echo $this->Form->control('filename', [
            'disabled' => true,
            'label' => I18N_FILENAME,
        ]);
        echo $this->Form->control('target', [
            'help' => __d('me_cms', 'The address should begin with {0}', '<em>http://</em>'),
            'label' => __d('me_cms', 'Web address'),
        ]);
        echo $this->Form->control('description', [
            'label' => I18N_DESCRIPTION,
            'rows' => 3,
            'type' => 'textarea',
        ]);
        ?>
    </fieldset>
</div>
<?= $this->Form->submit($title) ?>
<?= $this->Form->end() ?>
