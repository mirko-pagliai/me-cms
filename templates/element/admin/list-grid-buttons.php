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
 * @var \MeCms\View\View\Admin\AppView $this
 */
?>

<div class="mb-4">
    <div class="btn-group btn-group-sm" role="group">
        <?php
        echo $this->Html->button(
            __d('me_cms', 'Show as list'),
            ['?' => ['render' => 'list'] + $this->getRequest()->getQueryParams()],
            ['class' => 'btn-primary', 'icon' => 'align-justify']
        );
        echo $this->Html->button(
            __d('me_cms', 'Show as grid'),
            ['?' => ['render' => 'grid'] + $this->getRequest()->getQueryParams()],
            ['class' => 'btn-primary', 'icon' => 'th-large']
        );
        ?>
    </div>
</div>
