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
$this->assign('title', __d('me_cms', 'Upload banners'));
?>

<div class="card card-body bg-light border-0">
    <?= $this->Form->createInline(null, ['type' => 'get']) ?>
    <fieldset>
    <?php
        echo $this->Form->label('position', __d('me_cms', 'Position where to upload banners'));
        echo $this->Form->control('position', [
            'default' => $this->getRequest()->getQuery('position'),
            'label' => __d('me_cms', 'Position where to upload banners'),
            'onchange' => 'send_form(this)',
            'options' => $positions,
        ]);
        echo $this->Form->submit(I18N_SELECT);
    ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>

<?php
if ($this->getRequest()->getQuery('position')) {
    echo $this->element('admin/uploader');
}
?>