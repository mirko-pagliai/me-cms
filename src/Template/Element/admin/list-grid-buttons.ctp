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
?>

<div class="margin-20">
    <div class="btn-group btn-group-sm" role="group">
        <?= $this->Html->button(
            __d('me_cms', 'Show as list'),
            ['?' => array_merge($this->request->getQueryParams(), ['render' => 'list'])],
            ['class' => 'btn-primary', 'icon' => 'align-justify']
        ) ?>
        <?= $this->Html->button(
            __d('me_cms', 'Show as grid'),
            ['?' => array_merge($this->request->getQueryParams(), ['render' => 'grid'])],
            ['class' => 'btn-primary', 'icon' => 'th-large']
        ) ?>
    </div>
</div>