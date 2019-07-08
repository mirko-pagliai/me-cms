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
if ($banner->position->has('name')) {
    $class = sprintf('banner banner-%s', $banner->position->name);
} else {
    $class = 'banner';
}
$image = $this->Html->img($banner->path);
?>

<div class="<?= $class ?>">
    <?php
    if ($banner->has('target')) {
        echo $this->Html->link(
            $image,
            ['_name' => 'banner', $banner->id],
            ['target' => '_blank', 'title' => $banner->description]
        );
    } else {
        echo $image;
    }
    ?>
</div>