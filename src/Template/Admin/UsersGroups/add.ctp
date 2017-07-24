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
$this->assign('title', $title = __d('me_cms', 'Add users group'));
?>

<?= $this->Form->create($group); ?>
<fieldset>
    <?php
        echo $this->Form->control('name', [
            'label' => __d('me_cms', 'Name'),
        ]);
        echo $this->Form->control('label', [
            'label' => __d('me_cms', 'Label'),
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