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
$this->extend('/common/view'); ?>

<div class="text-center">
    <?php
    if (getConfig('default.offline_text')) {
        echo getConfig('default.offline_text');
    } else {
        echo __d('me_cms', 'The website is temporarily offline');
    }
    ?>
</div>
