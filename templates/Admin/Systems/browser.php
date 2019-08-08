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
$this->extend('/Admin/common/index');
$this->assign('title', __d('me_cms', 'Media browser'));

$this->Asset->script('MeCms.admin/kcfinder', ['block' => 'script_bottom']);
?>

<div class="card card-body bg-light border-0 mb-4">
    <?= $this->Form->createInline(null, ['type' => 'get']) ?>
    <fieldset>
    <?php
    echo $this->Form->label('type', __d('me_cms', 'Type'));
    echo $this->Form->control('type', [
        'default' => $this->getRequest()->getQuery('type'),
        'onchange' => 'send_form(this)',
    ]);
    echo $this->Form->submit(I18N_SELECT);
    ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>

<?php
if (!empty($kcfinder)) {
    echo $this->Html->iframe($kcfinder, ['id' => 'kcfinder', 'width' => '100%']);
}
