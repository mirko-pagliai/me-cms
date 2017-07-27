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

<div class="well">
    <?php
        echo $this->Form->createInline(null, ['type' => 'get']);
        echo $this->Form->label('position', __d('me_cms', 'Position to upload banners'));
        echo $this->Form->control('position', [
            'default' => $this->request->getQuery('position'),
            'label' => __d('me_cms', 'Position to upload banners'),
            'onchange' => 'send_form(this)',
            'options' => $positions,
        ]);
        echo $this->Form->submit(__d('me_cms', 'Select'));
        echo $this->Form->end();
    ?>
</div>

<?php
if ($this->request->getQuery('position')) {
    echo $this->element('admin/uploader');
}
?>