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
$class = 'banner';
if ($banner->get('position')->has('name')) {
    $class = sprintf('banner banner-%s', $banner->get('position')->get('name'));
}
?>

<div class="<?= $class ?>">
    <?php
    $image = $this->Html->img($banner->get('path'));
    if ($banner->has('target')) {
        $image = $this->Html->link(
            $image,
            ['_name' => 'banner', $banner->get('id')],
            ['target' => '_blank', 'title' => $banner->get('description')]
        );
    }
    echo $image;
    ?>
</div>
