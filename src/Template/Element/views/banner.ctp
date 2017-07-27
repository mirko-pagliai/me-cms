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
if ($banner->position->name) {
    $class = sprintf('banner banner-%s', $banner->position->name);
} else {
    $class = 'banner';
}
?>

<div class="<?= $class ?>">
    <?php
    if ($banner->target) {
        echo $this->Html->link(
            $this->Html->img($banner->path),
            ['_name' => 'banner', $banner->id],
            ['target' => '_blank', 'title' => $banner->description ?: null]
        );
    } else {
        echo $this->Html->img($banner->path);
    }
    ?>
</div>