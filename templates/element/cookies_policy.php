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
 * @var \MeCms\View\View\AppView $this
 */

//Returns for logged user
if ($this->Identity->isLoggedIn()) {
    return;
}

//Returns if disabled or already checked
if (!getConfig('default.cookies_policy') || !empty($_COOKIE['cookies-policy'])) {
    return;
}
?>

<div id="cookies-policy" class="sticky-top">
    <div class="container">
        <?php
        echo __d('me_cms', 'If you continue, you agree to the use of cookies, ok?');
        echo $this->Html->button(__d('me_cms', 'Ok'), ['_name' => 'acceptCookies'], ['class' => 'btn-sm btn-success', 'id' => 'cookies-policy-accept']);
        ?>
    </div>
</div>
