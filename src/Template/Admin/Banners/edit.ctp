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
$this->extend('/Admin/Common/form');
$this->assign('title', $title = __d('me_cms', 'Edit banner'));
?>

<?= $this->Form->create($banner); ?>
<div class='float-form'>
    <?php
        echo $this->Form->control('position_id', [
            'label' => __d('me_cms', 'Position'),
        ]);
        echo $this->Form->control('active', [
            'label' => sprintf('%s?', __d('me_cms', 'Published')),
        ]);
        echo $this->Form->control('thumbnail', [
            'label' => __d('me_cms', 'Thumbnail'),
            'help' => __d('me_cms', 'The banner is displayed as a  thumbnail. ' .
                'You should disable this, if the banner is an animated gif'),
        ]);
    ?>
</div>
<fieldset>
    <p><?= $this->Html->strong(__d('me_cms', 'Preview')) ?></p>
    <?php
    if ($banner->thumbnail) {
        echo $this->Thumb->resize($banner->path, ['width' => 1186], ['class' => 'img-thumbnail margin-15']);
    } else {
        echo $this->Html->img($banner->www, ['class' => 'img-thumbnail margin-15']);
    }

    echo $this->Form->control('filename', [
        'disabled' => true,
        'label' => __d('me_cms', 'Filename'),
    ]);
    echo $this->Form->control('target', [
        'label' => __d('me_cms', 'Web address'),
        'help' => __d('me_cms', 'The address should begin with {0}', $this->Html->em('http://')),
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