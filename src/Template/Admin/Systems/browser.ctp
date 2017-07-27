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
$this->extend('/Admin/Common/index');
$this->assign('title', __d('me_cms', 'Media browser'));

$this->Asset->script(ME_CMS . '.admin/kcfinder', ['block' => 'script_bottom']);
?>

<div class="well">
    <?php
        echo $this->Form->createInline(false, ['type' => 'get']);
        echo $this->Form->label('type', __d('me_cms', 'Type'));
        echo $this->Form->control('type', [
            'default' => $this->request->getQuery('type'),
            'onchange' => 'send_form(this)',
        ]);
        echo $this->Form->submit(__d('me_cms', 'Select'));
        echo $this->Form->end();
    ?>
</div>

<?php
if (!empty($kcfinder)) {
    echo $this->Html->iframe($kcfinder, ['id' => 'kcfinder', 'width' => '100%']);
}